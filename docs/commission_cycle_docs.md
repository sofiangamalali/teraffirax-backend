# The Commission Cycle

This document outlines the lifecycle of a commission from its creation to its payout, explaining the states, balances, and scheduled tasks that govern the process.

---

## Architecture & Balances

A Creator's balance is split into three fields to maintain accuracy and prevent transaction lock contention:
* **Pending Balance (`pending_balance`)**: Calculated commissions that are currently in their holding period.
* **Available Balance (`available_balance`)**: Matured commissions ready for withdrawal.
* **Withdrawn Balance (`withdrawn_balance`)**: Cumulative historical total of successfully completed withdrawals.

Every change in balance is backed by a corresponding record in the `balance_ledgers` table for auditable bookkeeping.

---

## The Lifecycle Stages

### 1. Commission Recording (Creation)
When a purchase occurs through a Creator's referral, a record is added to the `commissions` table.
* **Key Fields**: `earned_at` is set to the purchase date. `calculated_at` and `matured_at` are `null`.
* **Balance Impact**: **None.** Balances are not updated synchronously to keep order processing fast.

---

### 2. Initial Calculation (`commissions:calculate`)
* **Schedule**: Scheduled to run **every 3 hours**.
* **Process**:
  1. Finds all commissions where `calculated_at IS NULL`.
  2. Locks the creator's record (`lockForUpdate`) to prevent race conditions.
  3. Sums the uncalculated commissions and adds the total to the creator's `pending_balance`.
  4. Sets the `calculated_at` timestamp.
  5. Records a `commission_calculated` entry in the `balance_ledgers` table.

---

### 3. Maturation & Payout Availability (`commissions:mature`)
* **Schedule**: Triggers automatically **immediately after** `commissions:calculate` succeeds.
* **Process**:
  1. Identifies commissions that are calculated but not matured (`matured_at IS NULL` and `calculated_at IS NOT NULL`).
  2. Filters them based on the vendor's holding period:
     `earned_at <= now() - COALESCE(vendors.commission_payout_days, 90) days`
  3. Locks the creator's record.
  4. Subtracts the matured amount from `pending_balance` and adds it to `available_balance`.
  5. Sets the `matured_at` timestamp.
  6. Records a `commission_matured` entry in `balance_ledgers`.

---

### 4. Withdrawal Request
When a Creator initiates a payout request:
1. The system verifies there are no pending requests.
2. The entire `available_balance` is deducted and set to `0`.
3. A record is created in `withdraw_requests` with a `pending` status.
4. Records a `withdrawal_requested` entry (negative amount) in `balance_ledgers`.

---

### 5. Administrative Review
An Admin processes the withdrawal through one of three routes:
* **Approval (`approved`)**: Marks the request as approved. No balance changes occur yet.
* **Completion (`completed`)**: The admin transfers the money and records the transfer reference. The system updates the request to `completed` and increases the creator's `withdrawn_balance` to track historical payouts. Records `withdrawal_completed` in `balance_ledgers`.
* **Rejection (`rejected`)**: If rejected, the requested amount is returned to the creator's `available_balance`. Records `withdrawal_rejected` in `balance_ledgers`.

---

### 6. Automated Reconciliation (`balances:reconcile`)
* **Schedule**: Runs automatically **after** `commissions:mature` completes.
* **Process**: A read-only safety mechanism that recalculates each creator's balance from the ledger transaction history and compares it to the stored `creators` table values. If a mismatch is detected, a record is added to `reconciliation_alerts` for manual investigation.
