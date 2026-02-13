<?php
// Pastikan skrip ini hanya dijalankan sekali atau untuk tujuan pengujian
// Hati-hati jika data sudah ada, ini akan menambah data baru

include 'cek_koneksi.php'; // Sertakan file koneksi database Anda

// Jumlah data dummy yang ingin dibuat
$jumlah_data = 20;

echo "<h2>Mulai membuat $jumlah_data data dummy pengiriman...</h2>";

// Ambil ID dari tabel sopir, toko, dan produk
$sopir_ids = [];
$result_sopir = $conn->query("SELECT id FROM sopir");
while ($row = $result_sopir->fetch_assoc()) {
    $sopir_ids[] = $row['id'];
}

$toko_ids = [];
$result_toko = $conn->query("SELECT id FROM toko");
while ($row = $result_toko->fetch_assoc()) {
    $toko_ids[] = $row['id'];
}

$produk_ids = [];
$result_produk = $conn->query("SELECT id FROM produk");
while ($row = $result_produk->fetch_assoc()) {
    $produk_ids[] = $row['id'];
}

// Cek apakah data tersedia
if (empty($sopir_ids) || empty($toko_ids) || empty($produk_ids)) {
    die("<p style='color:red;'><strong>Data sopir, toko, atau produk tidak ada di database. Silakan isi terlebih dahulu.</strong></p>");
}

// Daftar status pengiriman
$statuses = ['Pending', 'Dalam Perjalanan', 'Terkirim', 'Ditolak'];
$alasan_ditolak = ['Stok kosong', 'Alamat tidak ditemukan', 'Toko tutup', 'Pembatalan dari pelanggan'];

// Looping untuk membuat data dummy
for ($i = 0; $i < $jumlah_data; $i++) {
    // Data acak
    $tanggal_acakan = date('Y-m-d', strtotime('-' . rand(0, 30) . ' days'));
    $sopir_id_acakan = $sopir_ids[array_rand($sopir_ids)];
    $produk_id_acakan = $produk_ids[array_rand($produk_ids)];
    $toko_id_acakan = $toko_ids[array_rand($toko_ids)];
    $jumlah_acakan = rand(10, 500);
    $harga_acakan = rand(100000, 5000000);
    $status_acakan = $statuses[array_rand($statuses)];
    
    $alasan_acakan = null;
    if ($status_acakan == 'Ditolak') {
        $alasan_acakan = $alasan_ditolak[array_rand($alasan_ditolak)];
    }

    $nomor_sj = "SJ" . str_replace('-', '', $tanggal_acakan) . rand(1000, 9999);

    // Mulai transaksi database
    $conn->begin_transaction();

    try {
        // 1. Masukkan data ke tabel surat_jalan
        $stmt_sj = $conn->prepare("INSERT INTO surat_jalan (nomor, tanggal, sopir_id, status, alasan) VALUES (?, ?, ?, ?, ?)");
        $stmt_sj->bind_param("ssiss", $nomor_sj, $tanggal_acakan, $sopir_id_acakan, $status_acakan, $alasan_acakan);
        if (!$stmt_sj->execute()) {
            throw new Exception("Error saat insert surat_jalan: " . $stmt_sj->error);
        }
        $surat_jalan_id = $stmt_sj->insert_id;
        $stmt_sj->close();

        // 2. Masukkan data ke tabel pengiriman
        $stmt_pengiriman = $conn->prepare("INSERT INTO pengiriman (surat_jalan_id, produk_id, toko_id, jumlah, harga) VALUES (?, ?, ?, ?, ?)");
        $stmt_pengiriman->bind_param("iiiid", $surat_jalan_id, $produk_id_acakan, $toko_id_acakan, $jumlah_acakan, $harga_acakan);
        if (!$stmt_pengiriman->execute()) {
            throw new Exception("Error saat insert pengiriman: " . $stmt_pengiriman->error);
        }
        $stmt_pengiriman->close();

        $conn->commit();
        echo "<p>✔️ Berhasil membuat surat jalan dengan nomor: <strong>$nomor_sj</strong></p>";

    } catch (Exception $e) {
        $conn->rollback();
        echo "<p style='color:red;'>❌ Gagal membuat data dummy: " . $e->getMessage() . "</p>";
    }
}

$conn->close();
echo "<br><h3>Proses selesai. Cek di halaman Status Pengiriman.</h3>";
?>