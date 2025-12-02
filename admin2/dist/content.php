<?php
// content.php
// Yêu cầu: $ketnoi (DB connection) đã tồn tại từ index.php trước khi include file này.

// Nếu một vài biến tổng chưa được định nghĩa ở index, tính dự phòng ở đây:
if (!isset($ketnoi)) {
    trigger_error('Database connection $ketnoi not found. Please include this file from index.php where $ketnoi exists.', E_USER_WARNING);
    $ketnoi = null;
}

function safe_count($ketnoi, $sql) {
    if (!$ketnoi) return 0;
    $res = $ketnoi->query($sql);
    if ($res) {
        $row = $res->fetch_assoc();
        return (int)($row['total'] ?? 0);
    }
    return 0;
}

if (!isset($total_articles)) {
    $total_articles = safe_count($ketnoi, "SELECT COUNT(*) AS total FROM articles");
}
if (!isset($total_users)) {
    $total_users = safe_count($ketnoi, "SELECT COUNT(*) AS total FROM users");
}
if (!isset($total_comments)) {
    $total_comments = safe_count($ketnoi, "SELECT COUNT(*) AS total FROM comments");
}
if (!isset($total_favorites)) {
    $total_favorites = safe_count($ketnoi, "SELECT COUNT(*) AS total FROM favorites");
}

// Charts data fallback
if (!isset($categories) || !isset($counts)) {
    $categories = $counts = [];
    if ($ketnoi) {
        $q = $ketnoi->query("SELECT c.name AS category, COUNT(a.article_id) AS cnt FROM categories c LEFT JOIN articles a ON c.category_id=a.category_id GROUP BY c.category_id");
        if ($q) {
            while ($r = $q->fetch_assoc()) {
                $categories[] = $r['category'];
                $counts[] = (int)$r['cnt'];
            }
        }
    }
}

if (!isset($roles) || !isset($role_counts)) {
    $roles = $role_counts = [];
    if ($ketnoi) {
        $qr = $ketnoi->query("SELECT role, COUNT(user_id) AS total FROM users GROUP BY role");
        if ($qr) {
            while ($r = $qr->fetch_assoc()) {
                $roles[] = ucfirst($r['role']);
                $role_counts[] = (int)$r['total'];
            }
        }
    }
}
?>

<!-- CONTENT: Dashboard (neon arcade grid + charts + lists) -->
<div class="content-wrapper">

  <!-- STYLES (scoped minimal) -->
  <style>
    /* Grid stat squares */
    .dashboard-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(210px, 1fr));
      gap: 18px;
      align-items: stretch;
      margin-bottom: 18px;
    }

    .stat-square {
      position: relative;
      background: radial-gradient(circle at 10% 10%, rgba(255,255,255,0.02), rgba(6,8,12,0.75));
      border-radius: 14px;
      padding: 18px;
      min-height: 140px;
      overflow: hidden;
      transition: transform .28s ease, box-shadow .28s ease;
      display: flex;
      flex-direction: column;
      justify-content: center;
      align-items: flex-start;
      border: 2px solid transparent;
    }

    .stat-square .icon {
      font-size: 22px;
      margin-bottom: 8px;
      filter: drop-shadow(0 6px 18px rgba(0,0,0,0.5));
    }

    .stat-square h5 { margin:0; font-size:0.95rem; font-weight:700; color:#e9ffff; }
    .stat-square h2 { margin:6px 0 0 0; font-size:2.2rem; color:#ffffff; letter-spacing:0.6px; }
    .stat-square p { margin:6px 0 0 0; font-size:0.85rem; color:rgba(255,255,255,0.65); }

    /* neon border overlay */
    .stat-square .neon-border { position:absolute; inset:0; border-radius:14px; pointer-events:none; }
    .stat-square:hover {
      transform: translateY(-6px) scale(1.01);
      box-shadow: 0 18px 44px rgba(255,75,225,0.06), inset 0 0 24px rgba(0,230,255,0.02);
    }

    /* quick actions */
    .quick-actions {
      display:flex;
      gap:10px;
      flex-wrap:wrap;
      justify-content:center;
      margin-bottom:18px;
    }
    .neon-action {
      padding:10px 14px;
      border-radius:12px;
      font-weight:700;
      text-decoration:none;
      display:inline-flex;
      align-items:center;
      gap:8px;
      cursor:pointer;
      transition: transform .18s, box-shadow .18s;
      color:#031018;
    }
    .neon-action:hover { transform: translateY(-3px); }

    .neon-action.blue{ background: linear-gradient(90deg, #00e6ff, #4db8ff); box-shadow: 0 10px 30px rgba(0,230,255,0.06); }
    .neon-action.purple{ background: linear-gradient(90deg, #b36bff, #ff4be1); color:#fff; box-shadow:0 10px 30px rgba(255,75,225,0.06); }
    .neon-action.green{ background: linear-gradient(90deg, #00ffa3, #66ffc3); box-shadow:0 10px 30px rgba(0,255,163,0.04); }
    .neon-action.pink{ background: linear-gradient(90deg, #ff8fd1, #ff4be1); box-shadow:0 10px 30px rgba(255,75,225,0.05); }

    /* charts containers adjustments */
    .chart-card { padding:12px; border-radius:12px; min-height:300px; position:relative; overflow:hidden; }
    .chart-card canvas { width:100% !important; height:280px !important; }

    /* lists */
    .neon-list { list-style:none; padding-left:0; margin:8px 0 0 0; color:#cfefff; }
    .neon-list li { padding:8px 6px; border-radius:8px; transition: background .12s; }
    .neon-list li:hover { background: rgba(255,255,255,0.02); transform: translateX(4px); }

    /* responsive small tweaks */
    @media (max-width: 768px) {
      .stat-square { min-height:120px; padding:14px; }
      .chart-card canvas { height:220px !important; }
    }

    /* small animated label style (for Chart micro-interactions) */
    .chart-badge {
      position:absolute;
      top:12px;
      right:12px;
      background:linear-gradient(90deg,#ff4be1,#00e6ff);
      color:#fff;
      padding:6px 10px;
      border-radius:999px;
      font-weight:700;
      font-size:0.85rem;
      box-shadow:0 8px 20px rgba(255,75,225,0.06);
      transform-origin: right top;
      transform: translateY(-6px) scale(.98);
      transition: transform .28s ease, opacity .28s;
      opacity:0.98;
    }
  </style>

  <!-- STAT SQUARES (grid horizontal) -->
  <div class="dashboard-grid" aria-label="Thống kê nhanh">
    <?php
      $cards = [
        ['icon'=>'📰','title'=>'BÀI VIẾT','count'=>$total_articles,'color'=>'#00e6ff','desc'=>'Tổng số bài viết'],
        ['icon'=>'👥','title'=>'NGƯỜI DÙNG','count'=>$total_users,'color'=>'#b36bff','desc'=>'Tổng tài khoản'],
        ['icon'=>'💬','title'=>'BÌNH LUẬN','count'=>$total_comments,'color'=>'#00ffa3','desc'=>'Tổng bình luận'],
        ['icon'=>'❤️','title'=>'YÊU THÍCH','count'=>$total_favorites,'color'=>'#ff4be1','desc'=>'Tổng lượt yêu thích']
      ];
      foreach ($cards as $c):
    ?>
      <div class="stat-square" style="border-color: <?= htmlspecialchars($c['color']) ?>20;">
        <div class="icon" style="color: <?= htmlspecialchars($c['color']) ?>;"><?= $c['icon'] ?></div>
        <h5 style="color: <?= htmlspecialchars($c['color']) ?>;"><?= $c['title'] ?></h5>
        <h2><?= number_format($c['count']) ?></h2>
        <p><?= htmlspecialchars($c['desc']) ?></p>
        <div class="neon-border" style="--neon-color: <?= htmlspecialchars($c['color']) ?>"></div>
      </div>
    <?php endforeach; ?>
  </div>

  <!-- QUICK ACTIONS -->
  <div style="margin-bottom:18px;text-align:center">
    <div class="quick-actions">
      <a class="neon-action blue" href="#section-users">👤 Quản lý người dùng</a>
      <a class="neon-action purple" href="#section-articles">📰 Quản lý bài viết</a>
      <a class="neon-action green" href="#section-comments">💬 Quản lý bình luận</a>
      <a class="neon-action pink" href="#section-favorites">❤️ Quản lý yêu thích</a>
    </div>
  </div>

  <!-- CHARTS -->
  <div class="row" style="margin-bottom:18px;">
    <div class="col-md-6" style="width:50%; padding:0 9px;">
      <div class="card chart-card neon-card">
        <div style="display:flex;align-items:center;justify-content:space-between">
          <h4 style="margin:0;color:#cfefff;">📊 Bài viết theo danh mục</h4>
          <div class="chart-badge" id="badge-cat">Top</div>
        </div>
        <canvas id="chartCategories"></canvas>
      </div>
    </div>

    <div class="col-md-6" style="width:50%; padding:0 9px;">
      <div class="card chart-card neon-card">
        <div style="display:flex;align-items:center;justify-content:space-between">
          <h4 style="margin:0;color:#cfefff;">🧑‍💻 Vai trò người dùng</h4>
          <div class="chart-badge" id="badge-role">Tỉ lệ</div>
        </div>
        <canvas id="chartRoles"></canvas>
      </div>
    </div>
  </div>

  <!-- RECENT LISTS -->
  <div id="section-users" class="row" style="margin-bottom:14px;">
    <div class="col-md-12" style="width:100%; padding:0 9px;">
      <div class="card neon-card p-3">
        <h4 style="margin:0 0 8px 0;color:#cfefff">Người dùng gần đây</h4>
        <?php
          $users = $ketnoi ? $ketnoi->query("SELECT * FROM users ORDER BY created_at DESC LIMIT 5") : null;
        ?>
        <div class="table-responsive" style="margin-top:8px;">
          <table class="table" style="width:100%">
            <thead>
              <tr>
                <th style="text-align:left;color:#cfefff">ID</th>
                <th style="text-align:left;color:#cfefff">Tên đăng nhập</th>
                <th style="text-align:left;color:#cfefff">Email</th>
                <th style="text-align:left;color:#cfefff">Quyền</th>
                <th style="text-align:left;color:#cfefff">Ngày tạo</th>
              </tr>
            </thead>
            <tbody>
              <?php if ($users): while($u = $users->fetch_assoc()): ?>
                <tr>
                  <td style="padding:8px 6px;"><?= $u['user_id'] ?></td>
                  <td><?= htmlspecialchars($u['username']) ?></td>
                  <td><?= htmlspecialchars($u['email']) ?></td>
                  <td><?= ucfirst($u['role']) ?></td>
                  <td><?= $u['created_at'] ?></td>
                </tr>
              <?php endwhile; else: ?>
                <tr><td colspan="5">Không có dữ liệu</td></tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

  <div id="section-articles" class="row" style="margin-bottom:14px;">
    <div class="col-md-12" style="width:100%; padding:0 9px;">
      <div class="card neon-card p-3">
        <h4 style="margin:0 0 8px 0;color:#cfefff">Bài viết gần đây</h4>
        <?php $arts = $ketnoi ? $ketnoi->query("SELECT title, created_at FROM articles ORDER BY created_at DESC LIMIT 5") : null; ?>
        <ul class="neon-list" style="margin-top:8px;">
          <?php if($arts): while($a = $arts->fetch_assoc()): ?>
            <li>📰 <b><?= htmlspecialchars($a['title']) ?></b> <small style="color:rgba(255,255,255,0.55)">(<?= $a['created_at'] ?>)</small></li>
          <?php endwhile; else: ?>
            <li>Không có dữ liệu</li>
          <?php endif; ?>
        </ul>
      </div>
    </div>
  </div>

  <div id="section-comments" class="row" style="margin-bottom:14px;">
    <div class="col-md-12" style="width:100%; padding:0 9px;">
      <div class="card neon-card p-3">
        <h4 style="margin:0 0 8px 0;color:#cfefff">Bình luận mới nhất</h4>
        <?php $coms = $ketnoi ? $ketnoi->query("SELECT c.content,u.username,c.created_at FROM comments c JOIN users u ON c.user_id=u.user_id ORDER BY c.created_at DESC LIMIT 5") : null; ?>
        <ul class="neon-list" style="margin-top:8px;">
          <?php if($coms): while($c = $coms->fetch_assoc()): ?>
            <li>💬 <b><?= htmlspecialchars($c['username']) ?></b>: <?= htmlspecialchars($c['content']) ?> <small style="color:rgba(255,255,255,0.55)">(<?= $c['created_at'] ?>)</small></li>
          <?php endwhile; else: ?>
            <li>Không có dữ liệu</li>
          <?php endif; ?>
        </ul>
      </div>
    </div>
  </div>

  <div id="section-favorites" class="row" style="margin-bottom:60px;">
    <div class="col-md-12" style="width:100%; padding:0 9px;">
      <div class="card neon-card p-3">
        <h4 style="margin:0 0 8px 0;color:#cfefff">Yêu thích gần đây</h4>
        <?php $favs = $ketnoi ? $ketnoi->query("SELECT f.created_at,u.username,a.title FROM favorites f JOIN users u ON f.user_id = u.user_id JOIN articles a ON f.article_id=a.article_id ORDER BY f.created_at DESC LIMIT 5") : null; ?>
        <ul class="neon-list" style="margin-top:8px;">
          <?php if($favs): while($f = $favs->fetch_assoc()): ?>
            <li>❤️ <b><?= htmlspecialchars($f['username']) ?></b> yêu thích <i><?= htmlspecialchars($f['title']) ?></i> <small style="color:rgba(255,255,255,0.55)">(<?= $f['created_at'] ?>)</small></li>
          <?php endwhile; else: ?>
            <li>Không có dữ liệu</li>
          <?php endif; ?>
        </ul>
      </div>
    </div>
  </div>

</div>

<!-- SCRIPTS: Chart interactions + micro-interactions -->
<script>
(function(){
  // Wait until Chart is loaded
  if (typeof Chart === 'undefined') return;

  // Data injected from PHP
  const categories = <?php echo json_encode(array_values($categories)); ?> || [];
  const counts = <?php echo json_encode(array_values($counts)); ?> || [];
  const roles = <?php echo json_encode(array_values($roles)); ?> || [];
  const roleCounts = <?php echo json_encode(array_values($role_counts)); ?> || [];

  // small helper gradient generator
  function makeGradient(ctx, color) {
    const g = ctx.createLinearGradient(0,0,0,300);
    g.addColorStop(0, color + 'CC');
    g.addColorStop(1, color + '22');
    return g;
  }

  // Bar chart: categories
  const catEl = document.getElementById('chartCategories');
  if (catEl) {
    const ctx = catEl.getContext('2d');
    const catChart = new Chart(ctx, {
      type: 'bar',
      data: {
        labels: categories,
        datasets: [{
          label: 'Số bài viết',
          data: counts,
          backgroundColor: function(context){
            const palette = ['#00e6ff','#b36bff','#00ffa3','#ff7ad1'];
            const color = palette[context.dataIndex % palette.length] || '#00e6ff';
            return makeGradient(context.chart.ctx, color);
          },
          borderColor: '#071216',
          borderWidth: 1.2,
          hoverBorderColor: '#ffffff'
        }]
      },
      options: {
        maintainAspectRatio: false,
        scales: {
          x: { ticks:{ color:'#dff7ff' }, grid:{ color:'rgba(255,255,255,0.02)' } },
          y: { ticks:{ color:'#dff7ff' }, beginAtZero:true, grid:{ color:'rgba(255,255,255,0.02)' } }
        },
        plugins: {
          legend: { display:false },
          tooltip: {
            enabled:true,
            backgroundColor: 'rgba(7,19,26,0.95)',
            titleColor: '#bffcff',
            bodyColor: '#eafcff',
            usePointStyle:true,
            callbacks: {
              label: function(ctx) {
                return ' ' + ctx.dataset.label + ': ' + ctx.formattedValue;
              }
            }
          }
        },
        interaction: { mode:'nearest', axis:'x', intersect:true },
        animation: { duration: 700, easing: 'easeOutCubic' },
        onHover: (evt, elements) => {
          evt.native.target.style.cursor = elements.length ? 'pointer' : 'default';
        }
      }
    });

    // micro-interaction: animated label showing value when hovering bar
    catEl.addEventListener('mousemove', (e) => {
      const points = catChart.getElementsAtEventForMode(e, 'nearest', { intersect: true }, true);
      const badge = document.getElementById('badge-cat');
      if (points.length) {
        const idx = points[0].index;
        badge.textContent = (categories[idx] ? categories[idx] : 'Item') + ': ' + (counts[idx] ?? 0);
        badge.style.transform = 'translateY(0) scale(1)';
        badge.style.opacity = '1';
      } else {
        badge.textContent = 'Top';
        badge.style.transform = 'translateY(-6px) scale(.98)';
        badge.style.opacity = '0.95';
      }
    });
  }

  // Doughnut chart: roles
  const roleEl = document.getElementById('chartRoles');
  if (roleEl) {
    const ctxR = roleEl.getContext('2d');
    const roleChart = new Chart(ctxR, {
      type: 'doughnut',
      data: {
        labels: roles,
        datasets: [{
          data: roleCounts,
          backgroundColor: ['#b36bff','#00ffa3','#ff7ad1','#00e6ff'],
          borderColor: '#071216',
          borderWidth: 2
        }]
      },
      options: {
        maintainAspectRatio: false,
        cutout: '60%',
        plugins: {
          legend: {
            position: 'bottom',
            labels: { color:'#dff7ff' }
          },
          tooltip: {
            backgroundColor: 'rgba(7,19,26,0.95)',
            titleColor: '#bffcff',
            bodyColor: '#eafcff'
          }
        },
        animation: { animateRotate:true, duration:1000, easing:'easeOutElastic' },
        onHover: (evt, elements) => {
          evt.native.target.style.cursor = elements.length ? 'pointer' : 'default';
          const badge = document.getElementById('badge-role');
          if (elements.length) {
            const idx = elements[0].index;
            badge.textContent = (roles[idx] ? roles[idx] : 'Role') + ': ' + (roleCounts[idx] ?? 0);
            badge.style.transform = 'translateY(0) scale(1)';
            badge.style.opacity = '1';
          } else {
            badge.textContent = 'Tỉ lệ';
            badge.style.transform = 'translateY(-6px) scale(.98)';
            badge.style.opacity = '0.95';
          }
        }
      }
    });
  }

  // Animated entrance for stat squares
  document.querySelectorAll('.stat-square').forEach((el, i) => {
    el.style.opacity = 0;
    el.style.transform = 'translateY(12px)';
    setTimeout(() => {
      el.style.transition = 'transform .5s cubic-bezier(.2,.9,.2,1), opacity .5s ease';
      el.style.transform = 'translateY(0)';
      el.style.opacity = 1;
    }, 120 + i * 80);
  });

  // Smooth scroll for quick actions anchors
  document.querySelectorAll('.neon-action').forEach(btn => {
    btn.addEventListener('click', (e) => {
      const href = btn.getAttribute('href');
      if (!href || !href.startsWith('#')) return;
      e.preventDefault();
      const t = document.querySelector(href);
      if (t) window.scrollTo({ top: t.offsetTop - 80, behavior: 'smooth' });
    });
  });

})();
</script>
