<?
use app\Helpers;
?>

<? if (!empty($this->schema_organization)) : ?>
    <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@type": "Organization",
      "name": "<?= Helpers::text($this->settings->company) ?>",
      "url": "https://<?= $_SERVER['SERVER_NAME'] ?>",
      "address": {
        "@type": "PostalAddress",
        "addressCountry": "RU",
        "addressLocality": "<?= Helpers::text($this->settings->city) ?>",
        "addressRegion": "<?= Helpers::text($this->settings->region) ?>",
        "postalCode": "<?= Helpers::text($this->settings->postcode) ?>",
        "streetAddress": "<?= Helpers::text($this->settings->address) ?>"
      },
      "telephone": "<?= $this->settings->phone ?>",
      "hasMerchantReturnPolicy": {
        "@type": "MerchantReturnPolicy",
        "applicableCountry": "RU",
        "returnPolicyCategory": "https://schema.org/MerchantReturnFiniteReturnWindow",
        "merchantReturnDays": "7",
        "returnFees": "https://schema.org/ReturnFeesCustomerResponsibility",
        "returnMethod": "https://schema.org/ReturnInStore"
      }
    }
    </script>
<? endif; ?>
