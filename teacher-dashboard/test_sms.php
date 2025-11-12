<form method="POST" action="send_sms.php">
  <label>Student ID:</label>
  <input type="text" name="student_id" value="97"><br>
  <label>Status:</label>
  <select name="status">
    <option value="Present">Present</option>
    <option value="Absent">Absent</option>
    <option value="Late">Late</option>
  </select><br><br>
  <button type="submit">Send Test SMS</button>
</form>
