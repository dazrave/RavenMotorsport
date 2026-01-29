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
- **Data File**: `payment_data.json` (auto-created in the public directory)
- **Access**: `/payments` (clean URL)

### Payment Structure (2026 Season)
- **Total Entry:** £3,350 × 2 teams = £6,700
- **Drivers:** 10 drivers (5 per team - Alpha & Bravo)
- **Cost per driver:** £670

**Payment Schedule:**
- **Deposit:** £200.00 per driver = £2,000 total
- **Installment 1:** £223.50 per driver = £2,233.32 total (£1,116.66 per team)
- **Installment 2:** £246.50 per driver = £2,466.68 total (remainder)
- **Team Kit:** Optional add-on fee (configurable)

### Features
- All driver payments visible on one page (no selection needed)
- Clean table layout with checkmarks for paid items
- Simple color scheme: Green checkmarks (✓) for paid, yellow amounts for partial, gray (-) for unpaid
- Drivers grouped by team (Alpha, Bravo, Management)
- Managers have separate section with no installment requirements
- Fully mobile responsive

### Admin Features
- **Payment Logging System**: Log individual payments as they come in
  - Select driver, payment type (deposit/installment 1/installment 2/team kit), and amount
  - Payments automatically accumulate (e.g., £100 now + £100 later = £200 total)
  - Tracks payment history with timestamps
  - Shows last 10 payments
- Manual edit mode for correcting totals
- Configure deadlines and expected amounts
- View total collected and outstanding balances

## Website Structure

- `index.php` - Homepage with team lineup and countdown (accessible at `/` or `/index`)
- `info.php` - Detailed team information and logistics (accessible at `/info`)
- `payments.php` - Payment tracking system (accessible at `/payments`)
- `payment_data.json` - Payment data storage (not in git)

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
