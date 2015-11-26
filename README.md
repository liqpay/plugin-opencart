plugin-opencart
===============

LiqPay plugin for opencart CMS

Releases (tags):

for Opencart versions since 1.5.4
- checkout3.0 - для работы с Liqpay API https://www.liqpay.com/ru/doc/checkout
- liq&buy3.0 - для работы с Liqpay API https://www.liqpay.com/ru/doc/liq_buy

for Opencart versions since 2.0
- liqpay_checkout3.0 - для работы с Liqpay API https://www.liqpay.com/ru/doc/checkout

#### Для установки модуля скопируйте его в каталог: ####

```
<OpenCart>/admin/
<OpenCart>/catalog/
```

#### Затем в панели администратора установите и настройте его: ####

```
Extensions > Payments > Liqpay нажать [Install]
```


#### Для начала приема платежей на Вашем сайте необходимо: ####
    - Зарегистрироваться на https://www.liqpay.com
    - Создать магазин в Вашем аккаунте используя мастер-установки (https://www.liqpay.com/admin/business)
    - Получите "Публичный ключ" и "Приватный ключ"
    
В некоторых сборках Opencart уже присутствует модуль Liqpay, который корректно работает с Liqpay API 2.0. Если вы хотите использовать новую версию для работы с Liqpay API 3.0, вы можете установить релиз liqpay_checkout3.0.
