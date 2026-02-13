<?php
// sidebar.php

$menu = [
    "📊   Dashboard" => [
        ["label" => "Dashboard", "link" => "dashboard.php"],
    ],
    "📦 Produk" => [
        ["label" => "🗁 Data Produk", "link" => "produk_list.php"],
    ],
    "🏛️ Toko" => [
        ["label" => "🗁 Data Toko", "link" => "toko_list.php"],
    ],
    "🚚 Pengiriman" => [
        ["label" => "✙ Tambah Pengiriman", "link" => "pengiriman_add.php"],
        ["label" => "➤ Status Pengiriman", "link" => "status_pengiriman.php"],
        ["label" => "🛠 Heuristik Pengiriman", "link" => "pengiriman_heuristik.php"],
    ],
    "👨 Sopir" => [
        ["label" => "🗁 Data Sopir", "link" => "sopir_list.php"],
        ["label" => "↪ Rute Sopir", "link" => "pilih_sopir.php"],
    ],
    "📢 Laporan" => [
        ["label" => "🗐 Laporan Dashboard", "link" => "laporan_dashboard.php"],
        ["label" => "🗐 Laporan Pengiriman", "link" => "laporan_pengiriman.php"],
    ],
];

$current_page = basename($_SERVER['PHP_SELF']);

function isActiveGroup($items, $current) {
    foreach ($items as $item) {
        if ($item['link'] === $current) return true;
    }
    return false;
}
?>

<nav class="sidebar" aria-label="Sidebar Navigation">
  <div class="sidebar-header">
    <div class="sidebar-logo">Toko Bangunan</div>
    <div class="sidebar-subtitle">Sinar Terang BSD</div>
  </div>

  <div class="menu-wrapper">
    <?php foreach ($menu as $groupName => $items): ?>
      <div class="menu-group">
        <div class="menu-title" role="button" tabindex="0"
             aria-expanded="<?= isActiveGroup($items, $current_page) ? 'true' : 'false' ?>"
             aria-controls="submenu-<?= md5($groupName) ?>"
             onclick="toggleSubmenu('submenu-<?= md5($groupName) ?>', this)"
             onkeypress="if(event.key === 'Enter') toggleSubmenu('submenu-<?= md5($groupName) ?>', this)">
          <?= htmlspecialchars($groupName) ?>
        </div>
        <div class="submenu <?= isActiveGroup($items, $current_page) ? 'open' : '' ?>" id="submenu-<?= md5($groupName) ?>">
          <?php foreach ($items as $item): ?>
            <a href="<?= htmlspecialchars($item['link']) ?>"
               class="<?= ($current_page == $item['link']) ? 'active' : '' ?>">
              <?= htmlspecialchars($item['label']) ?>
            </a>
          <?php endforeach; ?>
        </div>
      </div>
    <?php endforeach; ?>
  </div>

  <div class="logout-container">
    <a href="logout.php" class="logout" role="button" aria-label="Logout">Logout</a>
  </div>
</nav>

<style>
.sidebar {
  width: 260px;
  background-color: #2c3e50;
  color: #ecf0f1;
  display: flex;
  flex-direction: column;
  padding: 20px 15px;
  box-shadow: 2px 0 6px rgba(0,0,0,0.15);
  user-select: none;
}

/* Logo & Header */
.sidebar-header {
  text-align: center;
  margin-bottom: 25px;
  border-bottom: 1px solid #34495e;
  padding-bottom: 15px;
}

.sidebar-logo {
  font-size: 1.5rem;
  font-weight: bold;
  letter-spacing: 1px;
  line-height: 1.2;
}

.sidebar-subtitle {
  font-size: 1rem;
  font-weight: 500;
  color: #bdc3c7;
  margin-top: 5px;
  letter-spacing: 0.5px;
}

.menu-wrapper {
  flex-grow: 1;
  overflow-y: auto;
  scrollbar-width: none;
  -ms-overflow-style: none;
}

.menu-wrapper::-webkit-scrollbar {
  display: none;
}

.menu-group {
  margin-bottom: 20px;
}

.menu-title {
  font-weight: 700;
  font-size: 1.1rem;
  cursor: pointer;
  padding: 10px 15px;
  border-left: 4px solid transparent;
  transition: background-color 0.3s, border-color 0.3s;
  outline: none;
}
.menu-title:hover, .menu-title:focus {
  background-color: #34495e;
  border-left-color: #2980b9;
}

.submenu {
  max-height: 0;
  overflow: hidden;
  margin-left: 12px;
  border-left: 2px solid #34495e;
  transition: max-height 0.3s ease;
}

.submenu.open {
  max-height: 1000px;
  margin-bottom: 10px;
}

.submenu a {
  display: block;
  padding: 8px 20px;
  color: #bdc3c7;
  font-weight: 600;
  text-decoration: none;
  font-size: 0.95rem;
  border-radius: 4px;
  transition: background-color 0.3s;
}
.submenu a:hover,
.submenu a.active {
  background-color: #2980b9;
  color: white;
}

.logout-container {
  padding-top: 15px;
  border-top: 1px solid #34495e;
  margin-top: 20px;
}
.logout {
  background-color: #c0392b;
  color: white;
  text-align: center;
  border-radius: 6px;
  font-weight: 700;
  padding: 10px 0;
  display: block;
  text-decoration: none;
  user-select: none;
  transition: background-color 0.3s;
}
.logout:hover {
  background-color: #e74c3c;
}
</style>

<script>
function toggleSubmenu(id, elem) {
  const submenu = document.getElementById(id);
  const isOpen = submenu.classList.contains('open');
  if (isOpen) {
    submenu.classList.remove('open');
    elem.setAttribute('aria-expanded', 'false');
  } else {
    submenu.classList.add('open');
    elem.setAttribute('aria-expanded', 'true');
  }
}
</script>
