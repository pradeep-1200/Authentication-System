# AccountPortal

## Project Description
AccountPortal is a full-stack user management system developed as part of a GUVI internship assignment. The application demonstrates a secure and responsive authentication flow, allowing users to register, log in, view their profile dashboard, and update their personal information.

**Flow:** Register -> Login -> Profile Dashboard -> Update Profile

## Tech Stack
*   **Frontend:** HTML5, CSS3, JavaScript (jQuery), Bootstrap 5
*   **Backend:** PHP
*   **Database:** MySQL (Authentication), MongoDB (Profile Data)
*   **Session Management:** Redis (No PHP Sessions)
*   **AJAX:** jQuery

## Features
*   **User Registration:** Secure account creation with MySQL (for credentials) and MongoDB (for profile details).
*   **User Login:** Secure authentication using prepared statements.
*   **Profile Dashboard:** View user details including Name, Age, Date of Birth, and Contact.
*   **Profile Update:** Edit and update profile information seamlessly via AJAX.
*   **Secure Session Handling:** Custom session management using Redis and LocalStorage tokens.

## Folder Structure
The project maintains a strict separation of concerns:

*   **frontend/**: Contains all HTML pages, CSS stylesheets, and JavaScript files.
*   **backend/**: Contains PHP scripts for API endpoints and database configurations.
*   **backend/config/**: specific database connection files (MySQL, MongoDB, Redis).

## Rules Followed
This project strictly adheres to the GUVI internship guidelines:

*   **Separation of Concerns:** HTML, CSS, JavaScript, and PHP are in separate files.
*   **AJAX Only:** backend interaction is handled exclusively via jQuery AJAX; no form submissions.
*   **Database Security:** Authentication uses MySQL with Prepared Statements.
*   **No PHP Sessions:** Session management is handled manually via Redis and LocalStorage.
*   **Profile Data:** Extended user profile information is stored in MongoDB.
*   **Responsiveness:** UI is built using the Bootstrap framework.

## Setup Instructions
1.  **Clone the Repository:**
    Clone this project to your local machine or server.

2.  **Configure Databases:**
    *   Import the MySQL `users` table schema.
    *   Ensure MongoDB service is running and configured.
    *   Ensure Redis service is running.
    *   Update connection settings in `backend/config/`.

3.  **Run the Application:**
    Host the project on a local server (e.g., XAMPP/Apache) and navigate to the `frontend/login.html` page to start.

## Live Demo & Repository
*   **GitHub Repository:** https://github.com/pradeep-1200/Authentication-System.git
*   **Live Application:** [Link to Hosted Application]
