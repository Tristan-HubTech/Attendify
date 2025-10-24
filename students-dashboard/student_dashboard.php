
<?php
include 'db.php';

// For demo, we'll assume the student ID is fixed
$student_id = "2025-001";

// Load profile
$profile = mysqli_query($conn, "SELECT * FROM student_profile WHERE student_id='$student_id' LIMIT 1");
$profileData = mysqli_fetch_assoc($profile);

// Load subjects
$subjects = mysqli_query($conn, "SELECT * FROM subjects WHERE student_id='$student_id'");
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Student Dashboard — Attendance System</title>
<style>
/* same CSS from before */
:root{
  --bg:#0f1724;
  --card:#111827;
  --accent:#f59e0b;
  --muted:#94a3b8;
  --radius:12px;
  font-family:"Poppins",sans-serif;
}
body{
  margin:0;background:linear-gradient(180deg,var(--bg),#071024);
  color:#e6eef8;min-height:100vh;
}
header{
  display:flex;justify-content:space-between;align-items:center;
  background:var(--card);padding:16px 28px;
  box-shadow:0 3px 10px rgba(0,0,0,0.3);
  position:sticky;top:0;z-index:10;
}
header h1{font-size:22px;color:var(--accent);}
nav a{
  color:#e6eef8;text-decoration:none;margin-left:16px;
  padding:6px 10px;border-radius:8px;transition:.2s;
}
nav a:hover{background:rgba(255,255,255,0.1);}
.container{max-width:1000px;margin:30px auto;padding:0 20px;}
section{
  background:var(--card);
  margin-bottom:24px;
  border-radius:var(--radius);
  padding:20px;
  box-shadow:0 8px 25px rgba(0,0,0,0.5);
}
h2{color:var(--accent);margin-top:0;}
table{width:100%;border-collapse:collapse;margin-top:10px;}
th,td{border-bottom:1px solid rgba(255,255,255,0.05);padding:10px;text-align:left;}
th{color:var(--accent);}
button{
  background:var(--accent);color:#111;border:none;padding:6px 10px;
  border-radius:8px;cursor:pointer;font-weight:600;
}
button:hover{opacity:0.9;}
input, select{
  padding:6px 8px;border:none;border-radius:8px;
  background:#1e293b;color:white;
}
.form-row{display:flex;gap:10px;flex-wrap:wrap;margin-top:8px;}
.notice{color:var(--muted);font-size:14px;}
.profile-container{
  display:flex;align-items:center;gap:20px;
  background:var(--card);
  padding:20px;border-radius:var(--radius);
  box-shadow:0 5px 15px rgba(0,0,0,0.4);
  margin-bottom:24px;
}
.profile-img{
  width:100px;height:100px;border-radius:50%;object-fit:cover;
  border:3px solid var(--accent);
}
.profile-info p{margin:4px 0;}
</style>
</head>
<body>
<header>
  <h1>🎓 Student Dashboard</h1>
  <nav>
    <a href="#profile">Profile</a>
    <a href="#semester">Semester Schedule</a>
    <a href="#performance">Attendance Performance</a>
    <a href="#">Logout</a>
  </nav>
</header>

<div class="container">

  <!-- PROFILE SECTION -->
  <section id="profile">
    <h2>👤 Student Profile</h2>
    <div class="profile-container">
      <img class="profile-img" src="<?php echo $profileData['profile_img'] ?: 'https://via.placeholder.com/100'; ?>" alt="Profile Picture">
      <div class="profile-info">
        <p><strong>Name:</strong> <?php echo $profileData['name'] ?? 'N/A'; ?></p>
        <p><strong>Student ID:</strong> <?php echo $profileData['student_id'] ?? $student_id; ?></p>
        <p><strong>Course:</strong> <?php echo $profileData['course'] ?? 'N/A'; ?></p>
        <p><strong>Year Level:</strong> <?php echo $profileData['year_level'] ?? 'N/A'; ?></p>
      </div>
    </div>

    <form action="save_profile.php" method="POST" enctype="multipart/form-data">
      <input type="hidden" name="student_id" value="<?php echo $student_id; ?>">
      <div class="form-row">
        <input type="text" name="name" placeholder="Full Name" required>
        <input type="text" name="course" placeholder="Course" required>
        <input type="text" name="year_level" placeholder="Year Level" required>
        <input type="file" name="profile_img" accept="image/*">
        <button type="submit">Save Profile</button>
      </div>
    </form>
  </section>

  <!-- SEMESTER SCHEDULE -->
  <section id="semester">
    <h2>📚 Semester Class Schedule</h2>
    <form action="add_subject.php" method="POST">
      <input type="hidden" name="student_id" value="<?php echo $student_id; ?>">
      <div class="form-row">
        <input type="text" name="subject_name" placeholder="Subject Name" required>
        <select name="day" required>
          <option value="">Select Day</option>
          <option>Monday</option><option>Tuesday</option>
          <option>Wednesday</option><option>Thursday</option>
          <option>Friday</option><option>Saturday</option>
        </select>
        <input type="time" name="time" required>
        <button type="submit">Add Subject</button>
      </div>
    </form>

    <table>
      <thead><tr><th>Subject</th><th>Day</th><th>Time</th><th>Action</th></tr></thead>
      <tbody>
      <?php while($row = mysqli_fetch_assoc($subjects)): ?>
        <tr>
          <td><?= htmlspecialchars($row['subject_name']) ?></td>
          <td><?= htmlspecialchars($row['day']) ?></td>
          <td><?= htmlspecialchars($row['time']) ?></td>
          <td>
            <form action="delete_subject.php" method="POST" style="display:inline;">
              <input type="hidden" name="id" value="<?= $row['id'] ?>">
              <button type="submit">Delete</button>
            </form>
          </td>
        </tr>
      <?php endwhile; ?>
      </tbody>
    </table>
  </section>

  <!-- ATTENDANCE PERFORMANCE -->
  <section id="performance">
    <h2>📊 Attendance Performance</h2>
    <p class="notice">Below shows your attendance performance per subject (fetched from system).</p>
    <table id="subjectReport">
      <thead>
        <tr><th>Subject</th><th>Total Classes</th><th>Present</th><th>Absent</th><th>Attendance %</th></tr>
      </thead>
      <tbody>
        <tr><td>Math 101</td><td>20</td><td>18</td><td>2</td><td>90%</td></tr>
        <tr><td>Science 201</td><td>22</td><td>20</td><td>2</td><td>91%</td></tr>
        <tr><td>English 105</td><td>18</td><td>17</td><td>1</td><td>94%</td></tr>
      </tbody>
    </table>
  </section>
</div>
</body>
</html>
