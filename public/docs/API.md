# UMG API (v1)

Base URL (local): http://127.0.0.1:8000/api/v1

## Auth (Admin)
- POST /auth/login -> { token }
- GET  /auth/me (Bearer)

Header:
Authorization: Bearer <token>

## Newsletter (Admin)
- GET  /admin/newsletter/subscribers
- POST /admin/newsletter/subscribers
- PUT  /admin/newsletter/subscribers/{id}

- GET  /admin/newsletter/campaigns
- POST /admin/newsletter/campaigns
- POST /admin/newsletter/campaigns/{id}/send
- GET  /admin/newsletter/campaigns/{id}/stats
- POST /admin/newsletter/campaigns/{id}/finalize

## Newsletter (Public)
- POST /newsletter/subscribe (throttle:newsletter)
- POST /newsletter/unsubscribe (throttle:newsletter)