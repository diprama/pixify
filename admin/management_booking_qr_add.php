<?php
$_SESSION['SES_TITLE'] = "Re-Schedule Booking";
include_once "library/inc.seslogin.php";
include "header_v2.php";
$_SESSION['SES_PAGE'] = "?page=Management-Booking-Rescheduled";
$id = isset($_GET['id']) ? $_GET['id'] : '';



?>

<!-- select2 -->



<div class="app-content content ">
  <?php


  # Tombol Tambah diklik
  if (isset($_POST['btnTambah'])) {
    $id = isset($_GET['id']) ? $_GET['id'] : '';


    #data post
    $dataProduct  = $_POST['txtProduct'];
    $dataQty  = $_POST['txtQty'];
    $dataMetodePembayaran  = $_POST['txtMetodePembayaran'];

    $dataNominal  = isset($_POST['txtNominal']) ? $_POST['txtNominal'] : '';

    $ses_nama = $_SESSION['SES_NAMA'];

    #ambil harga
    $mySqlPrice = "SELECT * FROM `master_product` where id = '$dataProduct' ORDER BY id DESC LIMIT 1";
    $myQryPrice = mysqli_query($koneksidb, $mySqlPrice) or die("Query Insert Salah : " . mysqli_error($koneksidb));
    $DataPrice = mysqli_fetch_array($myQryPrice);

     if ($dataNominal =='') {
          $dataNominal = $DataPrice['price'];
    }
    $dataItem = $DataPrice['name'];
    $dataType = $DataPrice['type'];

    // kurangi qty
    if ($dataType == 'inventory') {
       $mySql1   = "INSERT INTO `master_product_stock`( `product_id`,`stock`,`updated_date`)
     VALUES ('$dataProduct','-$dataQty',now())";
      $myQry1   = mysqli_query($koneksidb, $mySql1)  or die("ERROR INPUT STOCK:  " . mysqli_error($koneksidb));
      $stock_order_id = mysqli_insert_id($koneksidb);
    }

    if ($id == '') {

      #tambah head qr
      $mySql   = "INSERT INTO `data_qr`( `updated_by`, `updated_date`)
     VALUES ('$ses_nama',now())";
      $myQry   = mysqli_query($koneksidb, $mySql)  or die("ERROR BOOKING:  " . mysqli_error($koneksidb));
      $id_transaction = mysqli_insert_id($koneksidb);

      #tambah detail qr
      $mySql   = "INSERT INTO `data_qr_detail`( `transaction_id`, `item`,`qty`, `nominal`, `stock_order_id`,`metode_pembayaran`)
     VALUES ('$id_transaction','$dataItem','$dataQty','$dataNominal','$stock_order_id','$dataMetodePembayaran')";
      $myQry   = mysqli_query($koneksidb, $mySql)  or die("ERROR BOOKING:  " . mysqli_error($koneksidb));
      $nomor  = 0;
    } else {
      #tambah detail qr
      $mySql   = "INSERT INTO `data_qr_detail`( `transaction_id`, `item`,`qty`, `nominal`, `stock_order_id`, `metode_pembayaran`)
     VALUES ('$id','$dataItem','$dataQty','$dataNominal','$stock_order_id','$dataMetodePembayaran')";
      $myQry   = mysqli_query($koneksidb, $mySql)  or die("ERROR BOOKING:  " . mysqli_error($koneksidb));
      $nomor  = 0;

      $id_transaction = $id;
    }







    # Validasi Insert Sukses
    if ($myQry) {
      echo "<meta http-equiv='refresh' content='0; url=?page=Management-Booking-QR-Add&id=$id_transaction'>";
    }
  }

  # Tombol Submit diklik
  // if (isset($_POST['btnSubmit'])) {
  //   # VALIDASI FORM, jika ada kotak yang kosong, buat pesan error ke dalam kotak $pesanError
  //   $pesanError = array();
  //   # Baca variabel form
  //   $id   = $_GET['id'];
  //   $dataGdrive  = $_POST['txtGdrive'];
  //   $dataDP  = $_POST['txtDP'];

  //   # UPDATE KE DATABASE BOOKING

  //   $mySql   = "UPDATE `booking` 
  //     SET `status`='Selesai',`updated_date`=now(), link_gdrive ='$dataGdrive', dp = '$dataDP' WHERE id='$id'";
  //   $myQry   = mysqli_query($koneksidb, $mySql)  or die("ERROR BOOKING:  " . mysqli_error($koneksidb));
  //   $nomor  = 0;

  //   if ($myQry) {
  //     echo "<meta http-equiv='refresh' content='0; url=?page=Print-Struk&id=$id&s=success'>";
  //   }
  // } // Penutup Tombol Submit

  # MASUKKAN DATA KE VARIABEL
  # TAMPILKAN DATA DARI DATABASE, Untuk ditampilkan kembali ke form edit
  $Code  = isset($_GET['id']) ?  $_GET['id'] : '';
  $mySql  = "SELECT * FROM data_qr WHERE transaction_id='$Code'";
  $myQry  = mysqli_query($koneksidb, $mySql)  or die("Query ambil data salah : " . mysqli_error());
  $myData = mysqli_fetch_array($myQry);
  # MASUKKAN DATA KE VARIABEL
  $dataCode    = isset($myData['transaction_id']) ? $myData['transaction_id'] : '';
  ?>
  <!-- BEGIN: Content-->
  <div class="content-overlay">
  </div>
  <div class="header-navbar-shadow">
  </div>
  <div class="content-wrapper">
    <div class="content-header">
      <div class="content-header-left col-md-9 col-12 mb-2">
        <div class="row breadcrumbs-top">
          <div class="col-12">
            <h2 class="content-header-title float-start mb-0">Cetak Struk Non Booking</h2>
            <div class="breadcrumb-wrapper">
              <ol class="breadcrumb">
              </ol>
            </div>
          </div>
        </div>
      </div>

    </div>

    <div class="content-body">
      <div class="row">
        <form action="<?php $_SERVER['PHP_SELF']; ?>" method="post" name="form1" target="_self" enctype="multipart/form-data">
          <div class="col-12">
            <div class="card">
              <div class="card-header border-bottom">
                <div class="content-header-right col-md-12 col-12 d-md-block d-none">

                  <div class="row">
                    <div class="card-body">

                      <div class="row">

                        <h3>Detail Transaksi</h3>

                        <div class="col-md-3 col-12">
                          <div class="form-group">
                              <label>Product</label>
                              <select id="productSelect" class="js-example-basic-single form-select" name="txtProduct" aria-label="Default select example" autocomplete="off" required onchange="toggleNominalField()">
                                  <option selected value="">Pilih</option>
                                  <?php
                                  $mySql  = "SELECT * from master_product group by `name` order by `name` asc";
                                  $myQry  = mysqli_query($koneksidb, $mySql) or die("RENTAS ERP ERROR : " . mysqli_error($koneksidb));
                                  while ($myData = mysqli_fetch_array($myQry)) {
                                      echo '<option value="' . $myData['id'] . '" data-type="' . $myData['type'] . '">' . $myData['name'] . '</option>';
                                  }
                                  ?>
                              </select>
                          </div>
                        </div>

                        <!-- Field Nominal (disembunyikan secara default) -->
                        <div class="col-md-3 col-12" id="nominalField" style="display: none;">
                          <div class="form-group">
                              <label>Nominal</label>
                              <input type="text" class="form-control" name="txtNominal" placeholder="Masukkan nominal">
                          </div>
                        </div>

                        <script>
                          function toggleNominalField() {
                              var productSelect = document.getElementById('productSelect');
                              var selectedOption = productSelect.options[productSelect.selectedIndex];
                              var productType = selectedOption.getAttribute('data-type');
                              var nominalField = document.getElementById('nominalField');
                              
                              if (productType === 'jasa') {
                                  nominalField.style.display = 'block';
                              } else {
                                  nominalField.style.display = 'none';
                              }
                          }
                        </script>


                        <div class="col-md-3 col-12">
                          <div class="form-group">
                            <label>Qty (Kuantiti)<span class="required">*</span></label>
                            <input class="form-control" placeholder="Qty" name="txtQty" type="number" value="" maxlength="100" required />
                          </div>
                        </div>

                           <div class="col-md-3 col-12">
                                       <label>Metode Pembayaran</label>
                                        <select class="select2 form-select" name="txtMetodePembayaran" aria-label="Default select example" autocomplete="off" required>
                                          <option value="">Pilih</option>
                                          <option value="Cash">Cash</option>
                                          <option value="Transfer Bank">Transfer Bank</option>
                                          <option value="QRIS">QRIS</option>
                                        </select>
                                   </div>
                        </div>

                  

                        <div class="col-md-3 col-12">
                          <br>
                          <button type=" submit" name="btnTambah" class="btn btn-info me-3">Tambah Item</button>
                        </div>

                      </div>


        </form>
        <form action="<?php $_SERVER['PHP_SELF']; ?>" method="post" name="form1" target="_self" enctype="multipart/form-data">
          <br>
          <div class="card-datatable">
            <table class="table datatables-basic table-striped table-bordered dt-responsive " cellspacing="0" width="100%">
              <thead>
                <tr>
                  <th>No</th>
                  <th>Item</th>
                  <th>Nominal</th>
                  <th>Qty</th>
                  <th>Hapus</th>
                  <!-- <th>Reschedule</th> -->
                </tr>
              </thead>
              <tbody>

                <?php
                $total_pembayaran = 0;
                $mySql   = "SELECT * FROM data_qr_detail where transaction_id='$id'  order by id asc";
                $myQry   = mysqli_query($koneksidb, $mySql)  or die("ERROR BOOKING:  " . mysqli_error($koneksidb));
                $nomor  = 0;
                while ($myData = mysqli_fetch_array($myQry)) {
                  $nomor++;
                  $Code = $myData['id'];
                  $Code2 = $myData['transaction_id'];


                ?>

                  <tr>
                    <td><?php echo $nomor; ?></td>
                    <td><?php echo $myData['item']; ?></td>
                    <td align="right"><?php echo 'RP. ' . number_format($myData['nominal'], 0); ?></td>
                    <td><?php echo $myData['qty']; ?></td>
                    <td>
                      <a href="?page=Management-Booking-QR-Delete&id=<?php echo $Code; ?>&id2=<?php echo $Code2; ?>" onclick="return confirm('INGIN HAPUS DATA?')" role="button"><i class="fa fa-pencil fa-fw">
                          <i data-feather="trash" class="me-50"></i>
                          <span></span>
                      </a>
                    </td>
                  </tr>

                <?php
                  // akumulasi total pembayaran
                  $total_pembayaran = $total_pembayaran +  ($myData['nominal'] * $myData['qty']);
                }
                ?>

              </tbody>
              <tfoot>
                <tr>
                  <td colspan="2" align="right">&nbsp;Total Pembayaran</td>
                  <td align="right"><?php echo  'RP. ' . number_format($total_pembayaran, 0); ?></td>
                  <td></td>
                </tr>
              </tfoot>
            </table>
          </div>
          <hr>

      </div>
      <div class="col-7 my-5">
        <a type="button" href="Management-Booking-QR" class="btn btn-warning me-2">Kembali</a>
        <?php
        if ($id != '') { ?>
          <a type="button" href="?page=Management-Booking-QR-Detail&id=<?= $id ?>" class="btn btn-info me-2">Submit</a>
        <?php }
        ?>
      </div>
    </div>
  </div>
</div>
</form>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
<!-- END: Content-->

<?php
include "footer_v2.php";
?>

<!-- <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
 -->

<script>
  $(document).ready(function() {
    $('.js-example-basic-single').select2();
});
</script>