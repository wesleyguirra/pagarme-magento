Feature: Checkout Pagar.me
    Like a seller that use Magento
    I want to allow that purchases must be paid using Checkout Pagar.me
    To make sales

    Scenario: Make a purchase by boleto
        Given a registered user
        When I access the store page
        And add any product to basket
        And I go to checkout page
        And login with registered user
        And confirm billing and shipping address information
        And choose pay with pagar me checkout using "Boleto bancário"
        And I confirm my personal data
        And finish purchase
        Then the purchase must be paid with success
        And a link to boleto must be provided

    Scenario: Make a purchase by credit card
        Given a registered user
        And a valid credit card
        When I access the store page
        And add any product to basket
        And I go to checkout page
        And login with registered user
        And confirm billing and shipping address information
        And choose pay with pagar me checkout using "Cartão de crédito"
        And I confirm my personal data
        And I confirm my payment information
        And finish purchase
        Then the purchase must be paid with success
