/**
 * js/comment.js
 * * ============================================================================
 * PROJECT LOGIC SUMMARY & DOCUMENTATION
 * ============================================================================
 * * 1. AUTHENTICATION (Login)
 * - login.php: The front-end entry point. Collects email and password.
 * - includes/login_process.php: 
 * - Uses PDO with prepared statements ($stmt and execute) for security.
 * - Verifies passwords using password_verify() against database hashes.
 * - Starts a session to persist user state.
 * 
 *  2. PASSWORD RESET (Forgot Password)
 * - forgot_password.php: Initial input form for user email.
 * - includes/get_question.php: 
 * - Queries database to retrieve the user's secret security question.
 * - Dynamically generates the next step form (the answer field).
 * - verify_answer.php: 
 * - Validates the user's answer against the hashed answer in the DB.
 * - Creates a temporary session ('reset_id') to authorize the next step.
 * - set_new_password.php:
 * - The final step: Updates the user's password record in the database.
 * 
 * * 3. DATABASE SECURITY (The "Why")
 * - $stmt (Prepared Statement): A blueprint that prevents SQL Injection.
 * - execute(): The action that binds user data to the blueprint safely.
 * - password_hash() / password_verify(): Ensures plain-text passwords 
 * are never stored or read, protecting user credentials.
 * 
 * 
 * * 4. AJAX (The Future State)
 * - We use fetch() requests to avoid page reloads.
 * - All AJAX handlers (PHP files) will return JSON, allowing 
 * the frontend (JS) to update the UI without refreshing the page.
 * ============================================================================
 */

// Placeholder for future AJAX shared function
// function sendAjaxRequest(url, formData, callback) {
//    // This will encapsulate the fetch() logic used across the site
// }

//console.log("Logic Documentation Loaded: System Architecture is clear.");