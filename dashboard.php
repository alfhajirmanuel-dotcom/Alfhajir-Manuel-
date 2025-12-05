<?php
session_start();
include 'config.php';
include 'header.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}
?>

<style>
.main {
 background: radial-gradient(circle at top left, #1f1c2c, #2c2742, #3a3058); 
  max-height: 130vh; /* Adjust height depending on how much you want visible */
  overflow-y: auto;
  padding-right: 8px; /* space for scrollbar */
}
/* Dashboard Cards */
.dashboard-cards {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
  gap: 18px;
  margin: 20px;
}

.dashboard-card {
  background: var(--panel, #fff);
  border-radius: 12px;
  border:solid #0591f7 2px;
  background: radial-gradient(circle at top left, #1f1c2c, #2c2742, #3a3058);
  padding: 20px;
  display: flex;
  flex-direction: column;
  justify-content: flex-start;
  transition: transform 0.2s ease, box-shadow 0.2s ease;
  text-decoration: none;
  color: inherit;
  
}

.dashboard-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 8px 24px rgba(0,0,0,0.08);
  
}

.dashboard-card h3 {
  font-size: 15px;
  color: var(--muted, #666);
  margin: 0 0 10px;
}

.dashboard-card p.count {
  font-size: 28px;
  font-weight: 200;
  color: var(--accent, #00bcd4);
  margin: 0 0 15px;
  background: radial-gradient(circle at top left, #1f1c2c, #2c2742, #3a3058);
}

/* Scrollable events list */
.event-list {
  max-height: 50px;
  overflow-y: auto;
  border-top: 1px solid #eee;
  padding-top: 8px;
}

.event-list::-webkit-scrollbar {
  width: 6px;
}
.event-list::-webkit-scrollbar-thumb {
  background-color: #ccc;
  border-radius: 4px;
}
.event-list div {
  padding: 6px 100;
  font-size: 14px;
  color: #333;
  border-bottom: 1px dashed #eee;
}
.event-list div:last-child {
  border-bottom: none;
}

.view-more {
  margin-top: 10px;
  font-size: 13px;
  text-align: right;
}
.view-more a {
  color: #00bcd4;
  text-decoration: none;
  font-weight: 600;
}
.view-more a:hover {
  text-decoration: underline;
}

@media (max-width: 600px) {
  .dashboard-cards {
    grid-template-columns: 1fr;
  }
  .dashboard-card p.count {
    font-size: 22px;
  }
}

.topbar{
 background: radial-gradient(circle at top left, #1f1c2c, #2c2742, #3a3058);
 color: #0591f7;
}

/* Match button theme with site colors */
.btn-remove {
  background: linear-gradient(135deg, #007bff, #00bcd4);
  border: none;
  color: #fff;
  font-size: 13px;
  font-weight: 700;
  border-radius: 8px;
  padding: 6px 12px;
  cursor: pointer;
  transition: all 0.2s ease;
  align-self: flex-start;
}

.btn-remove:hover {
  background: linear-gradient(135deg, #00bcd4, #007bff);
  box-shadow: 0 0 8px rgba(0, 188, 212, 0.6);
}



</style>

<?php
// âœ… Make sure DB connection is ready
if (!isset($conn) || !$conn) {
    die("<p style='color:red; text-align:center;'>Database connection failed.</p>");
}

// Counts
$total_events_query = $conn->query("SELECT COUNT(*) AS total FROM events");
$total_events = $total_events_query ? $total_events_query->fetch_assoc()['total'] : 0;

$new_events_query = $conn->query("SELECT COUNT(*) AS recent FROM events WHERE date >= CURDATE() - INTERVAL 7 DAY");
$new_events = $new_events_query ? $new_events_query->fetch_assoc()['recent'] : 0;

$upcoming_events_query = $conn->query("SELECT COUNT(*) AS upcoming FROM events WHERE date >= CURDATE()");
$upcoming_events = $upcoming_events_query ? $upcoming_events_query->fetch_assoc()['upcoming'] : 0;

// âœ… Fetch the actual new events (last 7 days)
$new_events_list = [];
$new_events_data = $conn->query("SELECT title, date FROM events WHERE date >= CURDATE() - INTERVAL 7 DAY ORDER BY date DESC LIMIT 10");
if ($new_events_data && $new_events_data->num_rows > 0) {
    while ($row = $new_events_data->fetch_assoc()) {
        $new_events_list[] = $row;
    }
}
?>

<div class="dashboard-cards">
  <!-- ðŸ†• New Events Card -->
  <div class="dashboard-card">
    <h3>🆕New Events (7 days)</h3>
    <p class="count"><?= htmlspecialchars($new_events) ?></p>
    <div class="event-list">
      <?php if (!empty($new_events_list)): ?>
        <?php foreach ($new_events_list as $event): ?>
          <div>
            <strong><?= htmlspecialchars($event['title']) ?></strong><br>
            <small style="color:#777;"><?= htmlspecialchars($event['date']) ?></small>
          </div>
        <?php endforeach; ?>
      <?php else: ?>
        <div style="color:#999;">No new events.</div>
      <?php endif; ?>
    </div>
    <div class="view-more">
      <a href="events_list.php?filter=new">View all </a>
    </div>
  </div>

  <!-- ðŸ“… Upcoming Events Card -->
  <div class="dashboard-card">
    <h3>📅 Upcoming Events</h3>
    <p class="count"><?= htmlspecialchars($upcoming_events) ?></p>
    <div class="view-more">
      <a href="events_list.php?filter=upcoming">View upcoming </a>
    </div>
  </div>
 
<?php
// ✅ Fetch user id
$stmt_user = $conn->prepare("SELECT id FROM users WHERE username = ?");
$stmt_user->bind_param("s", $_SESSION['username']);
$stmt_user->execute();
$res_user = $stmt_user->get_result();
$user = $res_user->fetch_assoc();
$user_id = $user['id'];

// ✅ Get user's volunteer applications
$applications_query = $conn->prepare("
    SELECT v.id AS volunteer_id, e.title, e.date, v.status 
    FROM volunteers v 
    JOIN events e ON v.event_id = e.id 
    WHERE v.user_id = ?
    ORDER BY e.date DESC
");
$applications_query->bind_param("i", $user_id);
$applications_query->execute();
$result_applications = $applications_query->get_result();

$my_applications = [];
if ($result_applications->num_rows > 0) {
    while ($row = $result_applications->fetch_assoc()) {
        $my_applications[] = $row;
    }
}
$total_applications = count($my_applications);
?>

 <!-- 🧍‍♂️ My Applications Card -->
<div class="dashboard-card">
  <h3>🧍‍♂️ My Applications</h3>
  <p class="count"><?= htmlspecialchars($total_applications) ?></p>

  <div class="event-list">
    <?php if (!empty($my_applications)): ?>
      <?php foreach ($my_applications as $app): ?>
        <div style="display:flex; flex-direction:column; gap:4px;">
          <strong style="color:#0591f7;"><?= htmlspecialchars($app['title']) ?></strong>
          <small style="color:#bbb;">📅 <?= htmlspecialchars($app['date']) ?></small>
          <small>Status:
            <?php if ($app['status'] == 'pending'): ?>
              <span style="color:#C1940A;">Pending</span>
            <?php elseif ($app['status'] == 'accepted'): ?>
              <span style="color:#06C91D;">Accepted</span>
            <?php else: ?>
              <span style="color:#FC0C0C;">Rejected</span>
            <?php endif; ?>
          </small>
          <form method="post" action="remove_application.php" 
                onsubmit="return confirmRemove();" 
                style="margin-top:6px;">
            <input type="hidden" name="volunteer_id" value="<?= intval($app['volunteer_id']) ?>">
            <button type="submit" class="btn-remove">Remove</button>
          </form>
        </div>
      <?php endforeach; ?>
    <?php else: ?>
      <div style="color:#999;">No applications yet.</div>
    <?php endif; ?>
  </div>

  <div class="view-more">
    <a href="my_applications.php">View all →</a>
  </div>
</div>
<?php
// ✅ Fetch total volunteer applicants (all users)
$applicants_query = $conn->query("
    SELECT v.id, v.status, e.title, u.username 
    FROM volunteers v
    JOIN events e ON v.event_id = e.id
    JOIN users u ON v.user_id = u.id
    ORDER BY v.id DESC
");

$volunteer_applications = [];
if ($applicants_query && $applicants_query->num_rows > 0) {
    while ($row = $applicants_query->fetch_assoc()) {
        $volunteer_applications[] = $row;
    }
}

// ✅ Calculate applicant stats
$total_applicants = count($volunteer_applications);
$pending_count = 0;
$accepted_count = 0;
$rejected_count = 0;

foreach ($volunteer_applications as $a) {
    if (strtolower($a['status']) === 'pending') $pending_count++;
    if (strtolower($a['status']) === 'accepted') $accepted_count++;
    if (strtolower($a['status']) === 'rejected') $rejected_count++;
}

// ✅ Get latest 5 applicants
$recent_applicants = array_slice($volunteer_applications, 0, 5);
?>

<!-- 🧾 Total Applicants Card -->
<div class="dashboard-card">
  <h3>🧾 Total Applicants</h3>
  <p class="count"><?= htmlspecialchars($total_applicants) ?></p>

  <div style="font-size:14px; margin-bottom:10px;color:#fff;">
    🕓 Pending: <strong style="color:#ffd64f;"><?= $pending_count ?></strong> |
    ✅ Accepted: <strong style="color:#05f75e;"><?= $accepted_count ?></strong> |
    ❌ Rejected: <strong style="color:#f55;"><?= $rejected_count ?></strong>
  </div>

  <div class="event-list">
    <?php if (!empty($recent_applicants)): ?>
      <?php foreach ($recent_applicants as $ra): ?>
        <div>
          <strong style="color:#0591f7;"><?= htmlspecialchars($ra['username']) ?></strong><br>
          <small style="color:#bbb;">Event: <?= htmlspecialchars($ra['title']) ?></small><br>
          <small>Status: 
            <?php if (strtolower($ra['status']) == 'pending'): ?>
              <span style="color:#C1940A;">Pending</span>
            <?php elseif (strtolower($ra['status']) == 'accepted'): ?>
              <span style="color:#06C91D;">Accepted</span>
            <?php else: ?>
              <span style="color:#FC0C0C;">Rejected</span>
            <?php endif; ?>
          </small>
        </div>
      <?php endforeach; ?>
    <?php else: ?>
      <div style="color:#999;">No applicants yet.</div>
    <?php endif; ?>
  </div>

  <div class="view-more">
    <a href="view_volunteers.php">View all →</a>
  </div>
</div>

<!-- ðŸ“Š Total Events Card -->
  <div class="dashboard-card">
    <h3>📋 Total Events</h3>
    <p class="count"><?= htmlspecialchars($total_events) ?></p>
    <div class="view-more">
      <a href="events_list.php?filter=all">View all </a>
    </div>
  </div>
</div>
 