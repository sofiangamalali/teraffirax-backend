# Scheduled Tasks (Cron Jobs) Documentation

This document explains the scheduled tasks (cron jobs) implemented in the project, including their logic, edge cases, and execution details.

## Scheduled Tasks

### 1. Calculate Daily Clicks (`calculate:daily-clicks`)

- **Command**: `php artisan calculate:daily-clicks {date?}`
- **Schedule**: Daily at 12:00 AM (`00:00`).
- **Definition File**: `routes/console.php`
- **Command File**: `app/Console/Commands/CalculateDailyClicks.php`
- **Service File**: `app/Services/DailyClickService.php`

#### Description
This command calculates and records the daily clicks for all creators. It uses a snapshot algorithm to determine how many clicks occurred on a specific day by comparing the lifetime total clicks against the last recorded snapshot.

#### Logic & Workflow
1. **Concurrency Control (Global Lock)**:
   - To prevent multiple instances of the job from running simultaneously, a global cache lock named `daily-clicks-job` is acquired for 600 seconds.
   - If the lock cannot be acquired, the job exits with a warning: "Job is already running. Exiting."

2. **Date Determination**:
   - The command accepts an optional `date` argument (format: `YYYY-MM-DD`).
   - If not provided, it defaults to **yesterday** (`now()->subDay()->toDateString()`).

3. **Chunk Processing**:
   - Creators are processed in chunks of 100 to optimize memory usage and prevent timeouts.

4. **Per-Creator Lock**:
   - For each creator, a lock named `calc-clicks-{$creator->id}` is acquired, blocking for up to 10 seconds. This ensures that calculations for the same creator don't overlap.

5. **Sequential Backfill (DailyClickService)**:
   - The service checks for the latest existing record in the `daily_clicks` table for the creator.
   - If records exist, it starts from the day after the last recorded date.
   - If no records exist, it starts from the creator's account creation date (`created_at`).
   - It iterates through every day from the start date up to the target date.

6. **The Snapshot Algorithm (O(1))**:
   - For each day being processed:
     - It fetches the latest record before that date to establish a **baseline** of total clicks up to that point.
     - It calculates the **current total** clicks by summing `clicks_count` from all tracking links belonging to the creator.
   - **Handling Missing Days (Edge Case)**:
     - If the processing date is *less than* the target date (meaning we are filling in missing days because the cron didn't run):
       - `clicks_count` for that day is set to `0`.
       - `total_snapshot` remains the same as the baseline.
     - If the processing date *is* the target date:
       - `clicks_count` is calculated as the difference: `max(0, currentTotal - baseline)`.
       - `total_snapshot` is updated to the `currentTotal`.
   - *Why this logic?* Since we only store the lifetime total of clicks on the tracking links, if the cron fails to run for a few days, we cannot know exactly which day the clicks occurred. Therefore, we fill the missing days with `0` and attribute all accumulated clicks to the day the cron actually runs.

7. **Failure Handling**:
   - If an exception occurs during the processing of a specific creator, the error is caught.
   - The failure is recorded in the `failed_daily_click_jobs` table (via `FailedDailyClickJobRepositoryInterface`).
   - The command continues processing the rest of the creators.

## Server Configuration

To enable the task scheduler on your production server, you must add the following single Cron entry to your server's crontab (via `crontab -e`):

```bash
* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
```

- This Cron entry calls the Laravel command scheduler every minute.
- Laravel then evaluates the scheduled tasks and runs them at the appropriate time (e.g., `00:00` for the daily clicks calculation).
