<!-- admin_nav.php -->
<div class="sidebar">
  <img src="../ama.png" alt="ACLC Logo">
  <h2>Admin Panel</h2>

  <a href="admin.php">🏠 Dashboard</a>
  <a href="manage_users.php">👥 Manage Users</a>
  <a href="manage_subjects.php">📘 Manage Subjects</a>
  <a href="manage_classes.php">🏫 Manage Classes</a>
  <a href="attendance_report.php">📊 Attendance Reports</a>
  <a href="assign_students.php">🎓 Assign Students</a>
  <a href="activity_log.php">🕒 Activity Log</a>
  <a href="user_feedback.php">💬 Feedback</a>
  <a href="../logout.php" class="logout">🚪 Logout</a>
</div>

<style>
:root {
  --primary-color: #17345f;
  --accent-color: #e21b23;
  --white: #ffffff;
}

/* SIDEBAR */
.sidebar {
    width: 210px;
    background: #17345f;
    color: white;
    height: 100vh;
    position: fixed;
    display: flex;
    flex-direction: column;
    align-items: center;
    padding-top: 15px;
}

.sidebar a:hover {
    background: #1d4b83;
    transform: translateX(4px);
}

.sidebar a.active {
    background: var(--accent-color);
    font-weight: 600;
    border-radius: 4px;
    transform: none;
}

.sidebar img {
    width: 60%;
    margin-bottom: 10px;
    user-select: none;
}
.sidebar h2 {
    font-size: 15px;
    margin-bottom: 20px;
    text-align: center;
    font-weight: 600;
    color: #f0f0f0;
    text-transform: uppercase;
}
.sidebar a {
    display: block;
    color: white;
    text-decoration: none;
    padding: 8px 15px;
    width: 85%;
    text-align: left;
    border-radius: 5px;
    margin: 3px 0;
    font-size: 14px;
    transition: 0.3s;
}
.logout {
    background: var(--accent-color);
    color: white;
    margin-top: auto;
    margin-bottom: 25px;
    text-align: center;
    border-radius: 6px;
    padding: 10px;
    width: 80%;
    font-size: 14px;
    transition: 0.25s ease;
}
.logout:hover {
    background: #b9161d;
    transform: translateY(-2px);
}

/* MAIN LAYOUT */
.main {
    margin-left: 210px;
    flex-grow: 1;
    display: flex;
    flex-direction: column;
    height: 100vh;
    background: #f4f6fa;
}

/* TOPBAR */
.topbar {
    background: white;
    padding: 12px 25px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    border-radius: 0 0 10px 10px;
}
.topbar h1 {
    margin: 0;
    color: var(--primary-color);
    font-size: 20px;
}

/* CONTENT */
.content {
    padding: 30px 25px;
}
h2 {
    color: var(--primary-color);
}

/* DASHBOARD CARDS */
.card-container {
    display: flex;
    gap: 20px;
    flex-wrap: wrap;
}
.card {
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 6px rgba(0,0,0,0.1);
    padding: 20px;
    width: 200px;
    text-align: center;
    transition: 0.2s ease;
}
.card:hover {
    transform: translateY(-3px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}
.card h3 {
    color: var(--primary-color);
    margin: 10px 0 5px;
}
</style>
