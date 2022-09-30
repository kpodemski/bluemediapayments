<?php

// autoload_classmap.php @generated by Composer

$vendorDir = dirname(__DIR__);
$baseDir = dirname($vendorDir);

return array(
    'BluePayment' => $baseDir . '/bluepayment.php',
    'BluePayment\\Adapter\\ConfigurationAdapter' => $baseDir . '/src/Adapter/ConfigurationAdapter.php',
    'BluePayment\\Analyse\\Amplitude' => $baseDir . '/src/Analyse/Amplitude.php',
    'BluePayment\\Analyse\\AnalyticsTracking' => $baseDir . '/src/Analyse/AnalyticsTracking.php',
    'BluePayment\\Api\\BlueAPI' => $baseDir . '/src/Api/BlueAPI.php',
    'BluePayment\\Api\\BlueGateway' => $baseDir . '/src/Api/BlueGateway.php',
    'BluePayment\\Api\\BlueGatewayChannels' => $baseDir . '/src/Api/BlueGatewayChannels.php',
    'BluePayment\\Api\\BlueGatewayTransfers' => $baseDir . '/src/Api/BlueGatewayTransfers.php',
    'BluePayment\\Api\\GatewayInterface' => $baseDir . '/src/Api/GatewayInterface.php',
    'BluePayment\\Configure\\Configure' => $baseDir . '/src/Configure/Configure.php',
    'BluePayment\\Hook\\AbstractHook' => $baseDir . '/src/Hook/AbstractHook.php',
    'BluePayment\\Hook\\Admin' => $baseDir . '/src/Hook/Admin.php',
    'BluePayment\\Hook\\Design' => $baseDir . '/src/Hook/Design.php',
    'BluePayment\\Hook\\HookDispatcher' => $baseDir . '/src/Hook/HookDispatcher.php',
    'BluePayment\\Hook\\Payment' => $baseDir . '/src/Hook/Payment.php',
    'BluePayment\\Install\\Installer' => $baseDir . '/src/Install/Installer.php',
    'BluePayment\\Service\\FactoryPaymentMethods' => $baseDir . '/src/Service/FactoryPaymentMethods.php',
    'BluePayment\\Service\\Gateway' => $baseDir . '/src/Service/Gateway.php',
    'BluePayment\\Service\\PaymentMethods\\AliorInstallment' => $baseDir . '/src/Service/PaymentMethods/AliorInstallment.php',
    'BluePayment\\Service\\PaymentMethods\\Blik' => $baseDir . '/src/Service/PaymentMethods/Blik.php',
    'BluePayment\\Service\\PaymentMethods\\Card' => $baseDir . '/src/Service/PaymentMethods/Card.php',
    'BluePayment\\Service\\PaymentMethods\\GatewayType' => $baseDir . '/src/Service/PaymentMethods/GatewayType.php',
    'BluePayment\\Service\\PaymentMethods\\InternetTransfer' => $baseDir . '/src/Service/PaymentMethods/InternetTransfer.php',
    'BluePayment\\Service\\PaymentMethods\\MainGateway' => $baseDir . '/src/Service/PaymentMethods/MainGateway.php',
    'BluePayment\\Service\\PaymentMethods\\Smartney' => $baseDir . '/src/Service/PaymentMethods/Smartney.php',
    'BluePayment\\Service\\PaymentMethods\\VirtualWallet' => $baseDir . '/src/Service/PaymentMethods/VirtualWallet.php',
    'BluePayment\\Service\\Refund' => $baseDir . '/src/Service/Refund.php',
    'BluePayment\\Service\\Transactions' => $baseDir . '/src/Service/Transactions.php',
    'BluePayment\\Statuses\\CustomStatus' => $baseDir . '/src/Statuses/CustomStatus.php',
    'BluePayment\\Statuses\\OrderStatusMessageDictionary' => $baseDir . '/src/Statuses/OrderStatusMessageDictionary.php',
    'BluePayment\\Until\\AdminHelper' => $baseDir . '/src/Until/AdminHelper.php',
    'BluePayment\\Until\\Helper' => $baseDir . '/src/Until/Helper.php',
    'Composer\\InstalledVersions' => $vendorDir . '/composer/InstalledVersions.php',
);
