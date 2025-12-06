<?php
$ketnoi = mysqli_connect('localhost', 'root', '', 'webtintuc');
if ($ketnoi) {
    mysqli_query($ketnoi, "SET NAMES 'UTF8'");
} else {
    die("Kết nối không thành công: " . mysqli_connect_error());
}
?>