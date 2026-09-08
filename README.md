# CineHub — Online Movie Management System

Plain HTML / CSS / PHP / MySQL cinema booking system (no frameworks).

## Setup (XAMPP)

1. Copy the whole `cinema` folder into `htdocs/` (so it's reachable at `http://localhost/cinema/`).
2. Start Apache and MySQL in XAMPP.
3. Open phpMyAdmin, click "Import", choose `config/schema.sql`, and run it.
   - This creates the `movie_management` database, all tables, and sample data (branches, halls, seats, movies, showtimes, offers, and demo accounts).
4. Check `config/db.php` — defaults (`localhost` / `root` / no password) match a stock XAMPP install. Change if yours differs.
5. Visit `http://localhost/cinema/index.php`.

## Demo accounts (from schema.sql seed data)

| Role | Email | Password |
|---|---|---|
| Admin | admin@cinema.com | admin123 |
| Counter Staff | staff@cinema.com | staff123 |
| Customer | (register your own via Sign Up) | — |

## Folder structure

- `config/` — `db.php` (connection), `schema.sql` (full DB schema + seed data)
- `includes/` — shared header/footer for the customer-facing site
- `admin/` — admin panel (movies, branches/halls, showtimes, offers, bookings, users) — guarded to `role = 'admin'`
- `counter/` — counter staff panel (QR lookup, check-in, print stubs) — guarded to `role = 'counter_staff'` or `'admin'`
- `vendor/phpqrcode/` — third-party QR code generation library (no Composer required, single dependency)
- `assets/qrcodes/` — QR code images get saved here automatically after each booking payment
- Root `.php` files — the customer-facing site (home, movie details, seat selection, checkout, tickets, bookings, loyalty)

## Core flow

1. Customer browses movies → selects a showtime → picks seats on the interactive seat map.
2. Seats are held for 10 minutes while checkout completes (`hold_seats.php`).
3. Optional offer code applied at checkout.
4. Dummy payment gateway (`process_payment.php`) confirms the booking, generates **one QR code per booking** (covers all seats), and awards loyalty points (1 point per LKR 100 spent).
5. Customer shows the QR at the counter. Staff look it up (`counter/scan.php`), see every seat in that booking, and print **individual physical stubs per seat** (`counter/print_ticket.php`) — each stub carries the same booking QR, matching real-world multiplex practice.
6. Staff mark seats as checked-in once stubs are handed out.

## Notes

- Payment is a dummy gateway — no real card processing, safe for a student project/demo.
- Pricing is a simple flat rule (Standard vs VIP) set in `seat_selection.php` and `hold_seats.php` — adjust the two constants there if you want per-branch or per-showtime pricing later.
- This schema and all functionality maps directly onto the submitted ER diagram — no entities, attributes, or relationships were added beyond what was already approved (see project chat history for the specific reasoning on QR-per-booking and the `role` field on `user`).
