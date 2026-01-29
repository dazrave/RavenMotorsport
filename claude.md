# Raven Motorsport - Development Notes

## Server Access

**Local Proxmox Server**
- Host: 192.168.0.23
- Username: root
- Password: EqualPurpleMouse
- Container: 101
- Website Path: `/var/www/RavenMotorsport/public`

## Deployment Process

The website is deployed in a Proxmox container with a git repository.

To deploy changes:
```bash
# 1. Commit and push to GitHub locally
git add .
git commit -m "Your message"
git push origin main

# 2. Pull changes on the server
sshpass -p 'EqualPurpleMouse' ssh -o StrictHostKeyChecking=no root@192.168.0.23 "pct exec 101 -- bash -c 'cd /var/www/RavenMotorsport/public && git pull origin main'"
```

## Payment Tracking System

- **Admin Passcode**: 3040
- **Database**: SQLite database (`payment_tracker.db`) - auto-created in the public directory
- **Access**: `/payments` (clean URL)

### Payment Structure (2026 Season)
- **Total Entry:** £3,350 × 2 teams = £6,700
- **Drivers:** 10 drivers (5 per team - Alpha & Bravo)
- **Cost per driver:** £670

**Default Payment Schedule:**
- **Deposit:** £200.00 per driver
- **Installment 1:** £223.50 per driver (Due: 1 Feb 2026)
- **Installment 2:** £246.50 per driver (Due: 1 Mar 2026)
- **Team Kit:** Optional add-on fee (configurable)

**Note**: Installments are now fully dynamic and can be added/edited/deleted from the admin panel.

### Features
- All driver payments visible on one page (no selection needed)
- Clean table layout with checkmarks for paid items
- Simple color scheme: Green checkmarks (✓) for paid, yellow amounts for partial, gray (-) for unpaid
- Drivers grouped by team (Alpha, Bravo, Management)
- Managers have separate section with no installment requirements
- Fully mobile responsive with abbreviated names on mobile (e.g., "D. Ravenscroft" instead of "Darren Ravenscroft")
- Payment methods displayed at the top of the page
- **Days to Go urgency indicator**: Each payment deadline shows days remaining with color-coded urgency:
  - Red: Overdue (creates pressure to pay immediately)
  - Orange: 7 days or less (urgent)
  - Yellow: 8-14 days (coming soon)
  - Green: 15+ days (on track)

### Admin Features
- **Dynamic Installment Management**:
  - Add unlimited installments with custom names, due dates, and amounts per driver
  - Edit existing installments (name, date, amount)
  - Delete installments (only if no payments have been made)
  - View total collected per team per installment
  - See what's left to collect for each installment
  - Days remaining shown for each installment with urgency indicators
- **Team Entry Costs**: Configure Alpha and Bravo team entry costs separately
- **Payment Logging System**: Log individual payments as they come in
  - Select driver, installment, and amount
  - Payments automatically accumulate (e.g., £100 now + £100 later = £200 total)
  - Tracks payment history with timestamps
  - Shows last 10 payments in "Recent Payments" section
- **Payment Deletion**: Delete individual payments from the system
  - Delete button available in Recent Payments section (last 10)
  - "All Payments" section shows complete payment history with delete options
  - Confirmation dialog before deletion
  - Displays payment amount, driver name, and team for verification
- View total collected and outstanding balances across all drivers
- Driver payments overview table showing all payments at a glance

## Website Structure

- `index.php` - Homepage with team lineup and countdown (accessible at `/` or `/index`)
- `info.php` - Detailed team information and logistics (accessible at `/info`)
- `payments.php` - Payment tracking system (accessible at `/payments`)
- `payment_tracker.db` - SQLite database for payment data (auto-created, not in git)

## Clean URLs

The site uses clean URLs without .php extensions:
- `/payments` instead of `/payments.php`
- `/info` instead of `/info.php`
- Nginx is configured with `try_files $uri $uri/ $uri.php?$args;` to handle this automatically

## Notes

- This is a local server (192.168.0.23), not publicly accessible
- Uses PHP for backend processing
- Bootstrap 5.3.3 for styling
- Theme color: #8b241d (Raven red)
