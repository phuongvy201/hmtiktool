<?php
echo '=== TEST AUTHORIZATION FIX ===
';
echo 'Integration ID: 30
';
echo 'Team ID: 7
';
echo 'Status: pending
';
echo 'Authorization URL: https://auth.tiktok-shops.com/oauth/authorize?app_key=6h5b0bsgaonml&state=7&redirect_uri=http%3A%2F%2Flocalhost%2Fteam%2Ftiktok-shop%2Fcallback&scope=seller.authorization.info%2Cseller.shop.info%2Cseller.product.basic%2Cseller.order.info%2Cseller.fulfillment.basic%2Cseller.logistics%2Cseller.delivery.status.write%2Cseller.finance.info%2Cseller.product.delete%2Cseller.product.write%2Cseller.product.optimize
';
echo 'Customer URL: https://auth.tiktok-shops.com/oauth/authorize?app_key=6h5b0bsgaonml&state=eyJ0ZWFtX2lkIjo3LCJhdXRoX3Rva2VuIjoiZml4X3Rva2VuXzE3NTg2MTI2MjciLCJ0eXBlIjoiY3VzdG9tZXJfYXV0aCJ9&redirect_uri=http%3A%2F%2Flocalhost%2Fpublic%2Fcustomer-callback&scope=seller.authorization.info%2Cseller.shop.info%2Cseller.product.basic%2Cseller.order.info%2Cseller.fulfillment.basic%2Cseller.logistics%2Cseller.delivery.status.write%2Cseller.finance.info%2Cseller.product.delete%2Cseller.product.write%2Cseller.product.optimize
';
echo '
';
echo 'Hướng dẫn sử dụng:
';
echo '1. Sử dụng Authorization URL để kết nối trực tiếp
';
echo '2. Hoặc sử dụng Customer Authorization URL để khách hàng lấy code
';
echo '3. Kiểm tra log để xem chi tiết quá trình authorization
';
?>