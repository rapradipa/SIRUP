<?php
namespace Database\Seeders;

use App\Models\Akun;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([[1,'Furniture'],[2,'Peralatan Dapur'],[3,'Peralatan Rumah Tangga'],[4,'Dekorasi & Interior'],[5,'Elektronik Rumah Tangga'],[6,'Perlengkapan Rumah'],[7,'Peralatan Kebersihan'],[8,'Peralatan Serbaguna']] as [$id, $n]) DB::table('kategori')->insert(['id_kategori' => $id, 'nama_kategori' => $n]);
        foreach ([[1,'PT Maju Jaya Furniture','Jakarta','081234567890'],[2,'PT Elektronik Nusantara','Semarang','085678901234']] as [$id, $n, $a, $t]) DB::table('supplier')->insert(['id_supplier' => $id, 'nama_supplier' => $n, 'alamat' => $a, 'no_telpon' => $t]);
        foreach ([[1,'Rizky Pratama','Ngawi','081111111111'],[2,'Siti Rahma','Ngawi','082222222222'],[3,'Agus Setiawan','Ngawi','083333333333']] as [$id, $n, $a, $t]) DB::table('karyawan')->insert(['id_karyawan' => $id, 'nama' => $n, 'alamat' => $a, 'no_telp' => $t]);
        foreach ([[1,'Andi Pratama','081234567801'],[2,'Budi Santoso','081234567802'],[3,'Citra Lestari','081234567803'],[4,'Dewi Anggraini','081234567804'],[5,'Eko Saputra','081234567805'],[6,'Fajar Nugroho','081234567806'],[7,'Gina Marlina','081234567807'],[8,'Hendra Wijaya','081234567808'],[9,'Intan Permata','081234567809'],[10,'Joko Susilo','081234567810']] as [$id, $n, $t]) DB::table('pelanggan')->insert(['id_pelanggan' => $id, 'nama' => $n, 'no_telp' => $t]);

        foreach ([
            [1,'Kursi Kayu',150000,20,1,1],
            [2,'Meja Belajar',250000,13,1,1],
            [3,'Lemari 2 Pintu',700000,13,1,1],
            [4,'Rak Buku',200000,13,1,1],
            [5,'Sofa Mini',900000,15,1,1],
            [6,'Meja TV',300000,13,1,1],
            [7,'Bangku Panjang',120000,19,1,1],
            [8,'Panci Aluminium',120000,15,2,1],
            [9,'Wajan Teflon',90000,20,2,1],
            [10,'Pisau Dapur',50000,25,2,1],
            [11,'Talenan',30000,30,2,1],
            [12,'Kompor Gas',200000,10,2,1],
            [13,'Rice Cooker',350000,8,2,2],
            [14,'Spatula',15000,40,2,1],
            [15,'Ember Plastik',20000,30,3,1],
            [16,'Gayung',10000,40,3,1],
            [17,'Rak Serbaguna',150000,12,3,1],
            [18,'Box Penyimpanan',60000,15,3,2],
            [19,'Keranjang',30000,20,3,1],
            [20,'Organizer',25000,14,3,2],
            [21,'Rak Plastik',200000,5,3,1],
            [22,'Vas Bunga',50000,6,4,2],
            [23,'Jam Dinding',80000,6,4,1],
            [24,'Bingkai Foto',120000,6,4,2],
            [25,'Karpet',150000,6,4,1],
            [26,'Lampu Hias',90000,9,4,2],
            [27,'Foto Frame',30000,20,4,1],
            [28,'Tanaman Hias',60000,10,4,2],
            [29,'Kipas Angin',250000,8,5,2],
            [30,'Blender',300000,6,5,2],
            [31,'Setrika',150000,10,5,2],
            [32,'Lampu LED',20000,50,5,2],
            [33,'Dispenser',400000,5,5,2],
            [34,'Rice Cooker Mini',250000,7,5,2],
            [35,'Hair Dryer',150000,10,5,2],
            [36,'Kasur Busa',500000,7,6,1],
            [37,'Bantal',30000,25,6,1],
            [38,'Guling',25000,20,6,1],
            [39,'Sprei',70000,18,6,1],
            [40,'Selimut',100000,10,6,1],
            [41,'Bed Cover',150000,8,6,1],
            [42,'Kasur Lipat',300000,6,6,1],
            [43,'Sapu Lantai',25000,20,7,1],
            [44,'Pel Lantai',45000,15,7,1],
            [45,'Sikat WC',15000,25,7,1],
            [46,'Lap Kain',10000,50,7,1],
            [47,'Tempat Sampah',50000,10,7,1],
            [48,'Kemoceng',12000,30,7,1],
            [49,'Spray Pembersih',35000,15,7,1],
            [50,'Box Plastik',60000,15,8,1],
            [51,'Rak Sepatu',120000,10,8,1],
            [52,'Keranjang Multifungsi',30000,20,8,1],
            [53,'Organizer Meja',25000,18,8,1],
            [54,'Laci Plastik',100000,8,8,1],
            [55,'Rak Dinding',90000,9,8,1],
            [56,'Tempat Mainan',70000,10,8,1]
        ] as [$id, $nama, $harga, $stok, $kat, $sup]) {
            DB::table('produk')->insert(['id_produk' => $id, 'nama_produk' => $nama, 'harga' => $harga, 'stok' => $stok,
                'id_kategori' => $kat, 'id_supplier' => $sup, 'diskon' => in_array($id, [1, 5, 13, 24]) ? 20 : 0]);
        }

        // Transaksi contoh (tanggal relatif agar grafik dashboard terisi): [id, hari lalu, pelanggan, karyawan, produk, jumlah, subtotal, bayar, kirim]
        foreach ([
            [1, 5, 1, 1, 20, 2, 50000, 'lunas', 'dikirim'], [2, 4, 2, 1, 21, 1, 200000, 'lunas', 'dikirim'],
            [3, 2, 3, 2, 22, 3, 150000, 'lunas', 'selesai'], [4, 1, 4, 2, 23, 2, 160000, 'lunas', 'diproses'],
            [5, 0, 5, 3, 24, 1, 120000, 'menunggu', 'menunggu'],
        ] as [$id, $hari, $pel, $kar, $prd, $jml, $sub, $bayar, $kirim]) {
            DB::table('transaksi')->insert(['id_transaksi' => $id, 'tanggal' => now()->subDays($hari)->toDateString(), 'total' => $sub, 'id_pelanggan' => $pel, 'id_karyawan' => $kar]);
            DB::table('detail_transaksi')->insert(['id_transaksi' => $id, 'id_produk' => $prd, 'jumlah' => $jml, 'subtotal' => $sub]);
            DB::table('pembayaran')->insert(['id_transaksi' => $id, 'jumlah' => $sub, 'status_pembayaran' => $bayar, 'metode' => 'transfer bank']);
            DB::table('pengiriman')->insert(['id_transaksi' => $id, 'alamat_pengiriman' => 'Ngawi', 'status_pengiriman' => $kirim]);
        }

        // Akun awal. GANTI password setelah login pertama.
        Akun::create(['no_telp' => '081111111111', 'password' => 'admin12345', 'role' => 'admin', 'id_karyawan' => 1]);
        Akun::create(['no_telp' => '082222222222', 'password' => 'kasir12345', 'role' => 'kasir', 'id_karyawan' => 2]);
        Akun::create(['no_telp' => '081234567801', 'password' => 'pelanggan123', 'role' => 'pelanggan', 'id_pelanggan' => 1]);
    }
}
