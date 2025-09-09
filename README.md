# This is a laravel way to interact with maishapay.com

[![Latest Version on Packagist](https://img.shields.io/packagist/v/uzhlaravel/maishapay.svg?style=flat-square)](https://packagist.org/packages/uzziahlukeka/maishapay)
![GitHub Tests Action Status](https://github.com/uzziahlukeka/maishapay/actions/workflows/run-tests.yml/badge.svg)
![GitHub Code Style Action Status](https://github.com/uzziahlukeka/maishapay/actions/workflows/fix-php-code-style-issues.yml/badge.svg)
[![Total Downloads](https://packagist.org/packages/uzziahlukeka/maishapay)
[![License](https://img.shields.io/packagist/l/uzhlaravel/maishapay.svg?style=flat-square)](https://packagist.org/packages/uzhlaravel/maishapay)

## Installation

You can install the package via composer:

```bash
composer require uzhlaravel/maishapay
```

You can publish and run the migrations with:

```bash
php artisan vendor:publish --tag="maishapay-migrations"
php artisan migrate
```

You can publish the config file with:

```bash
php artisan vendor:publish --tag="maishapay-config"
```

This is the contents of the published config file:

```php
return [

    'public_key' => env('MAISHAPAY_PUBLIC_KEY'),
    'secret_key' => env('MAISHAPAY_SECRET_KEY'),
    'gateway_mode' => env('MAISHAPAY_GATEWAY_MODE', 0),

];
```


## Usage Mobile Money Payment

```php

use Uzhlaravel\Maishapay\Maishapay;
use Uzhlaravel\Maishapay\Models\MaishapayTransaction;
use Uzhlaravel\Maishapay\DataTransferObjects\MobileMoney;
use Uzhlaravel\Maishapay\Exceptions\MaishapayException;

// you can use it as a parameter to a controller method

  protected $maishapay;

    public function __construct(Maishapay $maishapay)
    {
        $this->maishapay = $maishapay;
    }
    
    // for MOBILEMONEY payment type
      
    // validating the request data
    $validatedData = $request->validate([
            'amount' => 'required|numeric',
            'currency' => 'required|string',
            'customerFullName' => 'required|string',
            'customerEmailAddress' => 'required|email',
            'provider' => 'required|string',
            'walletID' => 'required',
            'channel' => 'required|string',
        ]);
        
    //if you want to keep track of the transaction
     $transaction = MaishapayTransaction::create([
                'payment_type' => 'MOBILEMONEY',
                'provider' => $validatedData['provider'],
                'amount' => $validatedData['amount'],
                'currency' => $validatedData['currency'],
                'customer_full_name' => $validatedData['customerFullName'],
                'customer_email' => $validatedData['customerEmailAddress'],
                'wallet_id' => $validatedData['walletID'],
                'channel' => $validatedData['channel'],
                'transaction_reference'=> 'TXN_' . uniqid(),
                'status' => 'PENDING'
            ]);
            
     //then do the transactin
     $mobileMoney = new MobileMoney(
                amount: $validatedData['amount'],
                currency: $validatedData['currency'],
                customerFullName: $validatedData['customerFullName'],
                customerEmailAddress: $validatedData['customerEmailAddress'],
                provider: $validatedData['provider'],
                walletId: $validatedData['walletID'],
                transactionReference: $transaction->transaction_reference,
                callbackUrl: route('pricing')
            );

            // Process mobile money payment
            $response = $this->maishapay->processMobileMoneyPayment($mobileMoney);

            // Parse the response
            $responseData = $response->json();
            
            
        // or you can use implemetation directly in your controller method
                   
            $maishapay = new Uzhlaravel\Maishapay\Maishapay();
        
             $response = $this->maishapay->processMobileMoneyPayment($mobileMoney);
             
             ...(other code)

```

## Usage Bank Payment 

```php

//importing the class

use Uzhlaravel\Maishapay\Maishapay;
use Uzhlaravel\Maishapay\Models\MaishapayTransaction;
use Uzhlaravel\Maishapay\DataTransferObjects\MobileMoney;
use Uzhlaravel\Maishapay\Exceptions\MaishapayException;

// you can use it as a parameter to a controller method

// for BANK payment type V2

// validating the request data
$validatedData = $request->validate([
            'amount' => 'required|numeric',
            'currency' => 'required|string',
            'customerFullName' => 'required|string',
            'customerEmailAddress' => 'required|email',
            'provider' => 'required|string',
            'walletID' => 'required',
            'channel' => 'required|string',
        ]);
        
//  if you want to keep track
$transaction = MaishapayTransaction::query()->create([
                'payment_type' => 'CARD',
                'provider' => $validatedData['provider'], //visa or mastercard
                'amount' => $validatedData['amount'],
                'currency' => $validatedData['currency'],
                'customer_full_name' => $validatedData['customerFullName'],
                'customer_phone' => $validatedData['customerPhoneNumber'],
                'customer_email' => $validatedData['customerEmailAddress'],
                'channel' =>  $validatedData['channel'],
                'transaction_reference'=> 'TXN_' . uniqid(),
                'status' => 'PENDING'
            ]);

            $cardPayment = new CardPayment(
                amount: $validatedData['amount'],
                currency: $validatedData['currency'],
                customerEmailAddress: $validatedData['customerEmailAddress'],
                customerPhoneNumber: $validatedData['customerPhoneNumber'],
                provider: $validatedData['provider'],
                customerFullName: $validatedData['customerFullName'],
                transactionReference: $transaction->transaction_reference,
                callbackUrl: route('your-callback-route') // if different from the one in your .env file
            );

            $response = $this->maishapay->processCardPayment($cardPayment);

            $responseData = $response->json();        
            
            //make sure to redirect to the payment page : 
            
             return redirect($responseData['paymentPage']);
        (other code)

```

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [uzziahlukeka](https://github.com/uzhlaravel)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
