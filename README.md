# HiPay Professional Gateway extension for Opencart 3

## API credentials

HiPay API production or sandbox account credentials for each currency:
   - merchant login
   - merchant password
   - website id
   - website category id: to find the category id, open one of the following urls in your browser and replace WEBSITEID for the real website id. For a production account use https://payment.hipay.com/order/list-categories/id/WEBSITEID and for a test account use https://test-payment.hipay.com/order/list-categories/id/WEBSITEID. Use one of the returned values.

## Setup
    
  - Sandbox: enable or disable sandbox/test account
  - Account credentials: for each currency enabled, set the API login, password, website id and category id
  - Account currency: choose from the list
  - Website Rating: choose the website rating from the list
  - Order title showing on payment window
  - Minimum and maximum amount to activate the payment method
  - Technical Email: this email will receive the notification alerts
  - Shopid (not mandatory)
  - Order coment for transaction (not mandatory)
  - Website Logo: full url for the logo that will appear on the HiPay payment window (not mandatory)
  - Debug: log payment info 
  - Order status for pending, cancelled, failed and paid transactions.
  - Geo zone: zones where the payment method is activated
  - Status: enable or disable the extension
  - Sort order: payment method checkout order
  
## Requirements
  - SOAP extension
  - SimpleXML

Version 1.0.0.2
