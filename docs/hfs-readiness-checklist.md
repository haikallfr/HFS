# HFS Readiness Checklist

Dokumen ini merangkum pekerjaan berikutnya agar HFS naik dari demo operasional ke sistem yang benar-benar siap dipakai klien.

## Prioritas Tinggi

- Stabilkan modul HM lapangan di perangkat Android nyata.
- Tambahkan indikator izin browser untuk kamera dan lokasi per perangkat.
- Sediakan daftar histori HM dengan filter tanggal, unit, dan export.
- Lengkapi workflow procurement `PR -> approval -> PO -> DO -> receiving`.
- Lengkapi inventory movement agar stok benar-benar bertambah saat DO diterima.
- Tambahkan audit trail yang konsisten untuk perubahan master dan transaksi.

## Prioritas Menengah

- Dashboard eksekutif realtime via Reverb untuk alert service, fuel discrepancy, dan input terakhir.
- Peta kerja unit berdasarkan area kerja terakhir.
- Halaman CRUD master data untuk unit, work area, inventory item, dan supplier.
- Approval matrix bertingkat untuk PR sesuai role.
- Notifikasi in-app untuk service due, fuel flag, dan approval tertunda.

## Hardening Sebelum Go-Live

- Validasi penuh pada browser mobile aktual di area sinyal lemah.
- Uji offline queue HM sampai sinkronisasi berhasil saat koneksi kembali.
- Backup database dan strategi restore.
- Log aktivitas user untuk login, approval, dan perubahan data kritikal.
- Kebijakan password, reset password, dan manajemen akun nonaktif.
- Monitoring error aplikasi dan log upload foto gagal.

## Catatan UX

- Pertahankan layout sederhana dengan panel yang bisa dilipat.
- Minimalkan jumlah informasi dalam satu layar.
- Pastikan status penting selalu muncul jelas: lokasi, kamera, sync, dan hasil simpan.
