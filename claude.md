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
- **Access**: `/payments.php`

### Features
- Driver portal: select name and view payment status
- Admin panel: manage all driver payments, deadlines, and totals
- Pre-loaded with deposit data from 2026 season

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
