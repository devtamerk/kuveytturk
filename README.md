# Kuveyt SanalPOS Entegrasyonu

<a class="bmc-button" target="_blank" href="https://www.buymeacoffee.com/tamerkirci"><img src="https://cdn.buymeacoffee.com/buttons/bmc-new-btn-logo.svg" alt="Buy me a coffee 😇"><span style="margin-left:5px;font-size:19px !important;">Buy me a coffee 😇</span></a>

Bu proje, Kuveyt Türk Bankası'nın SanalPOS ödeme sistemini entegre etmek için kullanılan PHP sınıfını içerir.

## Kullanım

1. **Kuveyt Sınıfını İnclude Edin**

   ```php
   require_once('Kuveyt.php');
   
2. **Sınıfı Başlatın**
   
   ```php
   $kuveyt = new Kuveyt('your_customer_id', 'your_merchant_id', 'your_classic_name', 'your_classic_password');

3. **Ödeme Başlatma**

   ```php
   $kuveyt->pay('card_name', 'card_number', 'card_month', 'card_year', 'card_cvv', 'amount','installment');

4. **Ödeme Onayla**

   ```php
   $confirm_result = $kuveyt->confirm($_POST);
   echo $confirm_result;

## Dikkat

Bu proje, Kuveyt Türk Bankası'nın SanalPOS servisini kullanır. Güvenlik ve lisans sorumluluklarına dikkat edin. Bu kodu kullanmadan önce Kuveyt Türk Bankası API belgelerini kontrol edin.

## Lisans

Bu proje MIT lisansı altında lisanslanmıştır.


Bu açıklama, projenin temel kullanım adımlarını ve dikkat edilmesi gereken önemli noktaları içerir. Ayrıca, projenin lisansı ve gereksinimleriyle ilgili bilgiler içerir. Gerçek projenize uyarlamadan önce gerekli düzenlemeleri yapmayı unutmayın.
