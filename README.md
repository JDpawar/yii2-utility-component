# Yii2 Utility Component

This component has multiple functions implemented which can be used in generalised ways.

Following things need to be configured before using the component.

1. Add the following code to your **app/config/web.php** file
    
    ```
    ...
    'components' => [
        ...
        'utility' => [
            'class' => 'app\components\Utility',
            'googleApiServerKey' => 'google_server_key',
        ],
        ...
    ],
    ...
    ```
    
2. Use the functions as follow

    ```
    Yii::$app->utility->getUniqueId();
    ```
    
    
##### Following are the functions that are used by most of the developers and implemented in this component

1. Get unique id based on micro time
2. Check validation of email
3. Check validation of mobile number starting with 7, 8, 9 only
4. Get distance between two points on earth using the *Great Haversine Distance Formula*
5. Get latitude and longitude of an address using the Google Places API
6. Download a file by giving its path
7. Convert a number with one base to other eg: base 10 to base 64
