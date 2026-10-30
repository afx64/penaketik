# PenakeTik

**Pena ke Tik** — an author-owned bookshop and reading room. One Laravel app gives a writer a public
storefront, a chapter-by-chapter publishing studio, and three ways to gate a book: public preview,
free for signed-in readers, limited-free for invited readers, and paid by manual bank transfer.

Built with **Laravel 13.39** on **PHP 8.5**, with SQLite by default and **no Node build step** —
deploying is copy files, run migrations, done. That is deliberate: the intended user is an author
with no DevOps background, often on shared hosting.

---

## Quick start

```bash
composer install
cp .env.example .env          # Windows: copy .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate --seed
php artisan storage:link
php artisan serve
```

Open <http://127.0.0.1:8000>.

### Demo accounts (created by the seeder)

| Account | Email | Password | Role |
| --- | --- | --- | --- |
| Author | `author@penaketik.test` | `password` | Owns two books, 8 chapters, bank details |
| Reader | `reader@penaketik.test` | `password` | Invited to the limited-free chapters |

Requirements: PHP 8.4+ (built for 8.5) with `pdo_sqlite`, `mbstring`, `openssl`, `fileinfo`, `gd`,
`zip`; Composer 2. No npm, no Vite build.

---

## How access works

Access is decided **per chapter** and the rule is evaluated on the server — locked chapters are never
sent to the browser.

| Access level | Who can read it |
| --- | --- |
| **Public preview** | Anyone. Only chapters in the first **3 positions** can be public. |
| **Free** | Any signed-in reader. |
| **Limited free** | Readers the author invited by email (dashboard → book → Access). |
| **Paid** | Readers whose bank transfer the author verified. Buying a book unlocks the whole book. |

Notes:

- The three-public-chapter cap is enforced both in the editor and on the server: saving a fourth
  public chapter downgrades it to Free, and a public chapter moved past position 3 stops being
  readable by guests.
- Reordering chapters is drag-and-drop, and it changes which chapters fall inside the preview window.
- Authors always see their own drafts and unpublished chapters.

## The author studio (`/dashboard`)

- **Books** — title, subtitle, genre, language, synopsis, cover, price, draft/published, featured,
  and per-book SEO overrides.
- **Chapters** — rich text editor (Quill 2) with headings, quotes, lists, links and image uploads
  stored on your own disk; per-chapter access level; publish toggle; drag-and-drop ordering.
- **Access** — invite registered readers by email for limited-free chapters; revoke later.
- **Orders** — download the transfer receipt, then approve (unlocks the book) or reject with a note.
- **Author profile** — pen name, bio, avatar, website, and "also available on" storefront links
  (site-wide or attached to a specific book).
- **SEO / Mail / Payments** — see below.

### Settings

- **SEO** — site tagline, default meta title/description/keywords, Google verification code. Every
  page renders canonical, Open Graph and Twitter tags; book pages use the book cover as the social
  image and locked chapters are marked `noarchive, nosnippet`.
- **Mail** — SMTP host/port/user/password/encryption and from-address, saved in the database and
  applied at runtime (no `.env` edit). A test-send button is included. With the mailer set to
  *Log only* (the default), messages land in `storage/logs/laravel.log`.
- **Payments** — currency plus bank name, account holder and number, and reader instructions. Orders
  cannot be placed until an account number is saved.

## Content protection (and its honest limits)

The reader page ships `public/js/reader-guard.js`, which:

- blocks right click, text selection, copy/cut, drag and `Ctrl/Cmd+A`;
- blocks `F12`, `Ctrl+Shift+I/J/C/K`, `Ctrl+U`, `Ctrl+S`, `Ctrl+P`;
- clears the clipboard on `PrintScreen` and blocks printing via CSS and `beforeprint`;
- shows a "content protected" veil when browser devtools are detected (gap heuristic, desktop only);
- tiles a per-reader watermark (email, IP, timestamp) over the chapter, so a screenshot or copied
  text can be traced back to the account that opened it.

**What it cannot do:** no website can prevent a phone camera or an OS-level screenshot, and anyone
who controls the browser can read the HTML their session was served. The realistic goal is deterrence
plus traceability. Treat the watermark, not the key blocking, as the actual protection. Server-side,
the important part is real: locked chapters are never included in the response.

## Deployment without DevOps

1. Upload the project (or `git pull`) — the document root must be the `public/` directory.
2. `composer install --no-dev --optimize-autoloader`
3. `php artisan key:generate`, `php artisan migrate --force`, `php artisan storage:link`
4. `php artisan config:cache && php artisan route:cache && php artisan view:cache`
5. Point `APP_URL` at the real domain, set `APP_ENV=production` and `APP_DEBUG=false`.

SQLite (`database/database.sqlite`) is the default because it is one file and needs no server. If the
host provides MySQL, change `DB_CONNECTION` and credentials in `.env` — the migrations are portable.

## Project map

```
app/Http/Controllers/            public storefront, auth, purchases
app/Http/Controllers/Dashboard/  author studio (books, chapters, access, orders, settings)
app/Models/                      Book, Chapter, AccessGrant, Order, Setting, StoreLink, User
app/Policies/                    book ownership + order visibility rules
app/Support/                     Settings store, HtmlSanitizer, MailConfigurator, Notifier
public/css/penaketik.css         the entire stylesheet (no build step)
public/js/reader-guard.js        content protection + watermark
public/js/editor.js              Quill editor + image upload
public/js/book-editor.js         drag-and-drop chapter ordering
resources/views/                 Blade templates (public, reader, dashboard, auth)
```

## Tests

```bash
php artisan test
```

`tests/Feature/ChapterAccessTest.php` covers the access matrix: guest preview limit, login for free
chapters, invitation for limited chapters, verified order for paid chapters, and cross-author
edit attempts. `ExampleTest` covers the public home page.

## Design decisions worth knowing

- **Access is per chapter, purchase is per book.** A book has one price; buying it unlocks every paid
  chapter, and future paid chapters too.
- **The preview cap is enforced server-side**, not just hidden in the UI.
- **Author HTML is sanitized** against an allowlist (`App\Support\HtmlSanitizer`) before storage, since
  readers render it.
- **Payment is manual by design** — bank transfer with receipt upload and author approval, because
  that is what works without a payment gateway account.
- **No front-end build** — a single hand-written CSS file and plain JS, so a PHP-only host can serve it.
