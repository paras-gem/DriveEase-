/* SUPPORT-DESK/
 * ├── .github/workflows/deploy.yml # Automated InfinityFree FTP deployment pipeline
 * ├── api/                         # Background headless data layers (Asynchronous AJAX Fetch)
 * │   ├── availabilty.php          # Real-time vehicle date conflict scanner
 * │   ├── bookings.php             # Multi-car booking generation coordinator
 * │   ├── customers.php            # Direct customer row management endpoint
 * │   └── fleet.php                # Asynchronous live query engine for search updates
 * ├── assets/                      # Core static presentation dependencies
 * │   ├── css/style.css            # Root UI styling + Light/Dark variable tokens
 * │   └── js/                      # Frontend system logic scripts
 * │       ├── bookings.js          # Cart item calculation & selection tracking
 * │       ├── fleet.js             # Form interceptor & client-side asynchronous UI renderer
 * │       ├── main.js              # Application core orchestration scripts
 * │       └── theme-toggle.js      # Dynamic mode toggler (Saves setting to local storage + DB)
 * ├── config/db.php                # Secure, centralized live PDO connection framework
 * ├── includes/                    # Reusable page components
 * │   ├── header.php               # Top structural element & dynamic theme interpreter
 * │   ├── footer.php               # Closes page wrappers & injects scripts
 * │   ├── search-form.php          # Reusable multi-car UI input panel
 * │   └── sidebar.php              # Back-end admin panel sidebar frame
 * ├── public/                      # [EMPTY BY DESIGN]: Avoided nested directory structures
 * │                                # to keep server file system pathways direct and robust.
 * ├── index.php                    # Server entry point (redirects to login)
 * ├── dashboard.php                # Authenticated landing page environment
 * ├── database.sql                 # Repository replica of current database schemas
 * ├── fetch-fleet.php              # Standard fallback PHP server-side data fetching module
 * ├── login.php                    # Authentication gate (Traditional Credentials + Google Auth Hook)
 * ├── logout.php                   # Explicit user session destruction routine
 * └── Readme.md                    # Project installation and operations guidebook 
 * 
 * 1. WEB HOSTING ENVIRONMENT (INFINITYFREE)
 * - Direct Root Execution: Main processing files execute directly from the 
 * htdocs root folder. This completely removes path complications during automated 
 * Git deployments.
 * * 2. DATABASE REPLICA ENGINE (MySQL via phpMyAdmin)
 * - Tables Built: Total of 7 relational tables (users, customers, vehicles, 
 * bookings, payments, maintenance, support_tickets).
 * - Sequential Database Indexes: Applied as an independent final batch at the 
 * very tail end of database operations. This optimizes search lookups for 
 * vehicle status, customer tracking, and booking schedules.
 * * 3. PDO CONNECTION INFRASTRUCTURE (PHP Data Objects)
 * - Definition: A data abstraction database middleware driver in PHP.
 * - Security Layer: Enforces prepared query statement sanitization natively 
 * to block SQL Injection vectors.
 * - Portability: Unifies how PHP communicates with databases. If the backend engine 
 * changes, the application layer remains completely untouched. /*