<?

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");
$APPLICATION->SetTitle("Интернет-магазин \"Одежда\"");
?>
<div class="section__container">

    <? 
    ?>
</div>
<?

$APPLICATION->IncludeComponent(
    "beeralex:content",
    ".default",
    [
        "CACHE_TYPE" => "A",
        "CACHE_TIME" => "86400",
    ]
);

?>

<?
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php");
?>