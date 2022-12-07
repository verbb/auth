<?php
namespace verbb\auth\models;

use craft\helpers\ArrayHelper;
use craft\helpers\Json;

use League\OAuth1\Client\Server\User as OAuth1Profile;
use League\OAuth2\Client\Provider\ResourceOwnerInterface as OAuth2Profile;

use ReflectionClass;

class UserProfile
{
    // Properties
    // =========================================================================

    public array $data = [];

    public function __construct(OAuth1Profile|OAuth2Profile $resource)
    {
        // Serialize a OAuth2 Resource to an array. Take all the public methods and use those, 
        // falling back on the raw data/response captured. This is because some methods modify the
        // data, or do other tasks (such as fetching remote images), so they take precedence.
        if ($resource instanceof OAuth2Profile) {
            $class = new ReflectionClass($resource);
            $methods = $class->getMethods();

            // Store the native `toArray()` as the response.
            $this->data['response'] = $resource->toArray();

            foreach ($methods as $method) {
                if ($method->isPublic() && $method->getNumberOfParameters() === 0 && str_starts_with($method->name, 'get')) {
                    $attributeName = lcfirst(preg_replace('/^get/', '', $method->name));

                    $this->data[$attributeName] = $resource->{$method->name}();
                }
            }
        }

        if ($resource instanceof OAuth1Profile) {
            // Store the native `toArray()` as the response.
            $this->data['response'] = $data = Json::decode(Json::encode($resource));

            // Normalise to match OAuth2
            $this->data['id'] = $data['uid'];
            $this->data['nickname'] = $data['nickname'];
            $this->data['name'] = $data['name'];
            $this->data['firstName'] = $data['firstName'];
            $this->data['lastName'] = $data['lastName'];
            $this->data['email'] = $data['email'];
            $this->data['location'] = $data['location'];
            $this->data['description'] = $data['description'];
            $this->data['imageUrl'] = $data['imageUrl'];
        }
    }

    public function __get($name)
    {
        $response = $this->data['response'] ?? [];

        // Override normal model behaviour to look everything up in the array.
        return ArrayHelper::getValue($this->data, $name) ?? ArrayHelper::getValue($response, $name);
    }

    public function __isset($name)
    {

    }

    public function __set($name, $value)
    {

    }

    public function toArray(): array
    {
        return $this->data;
    }
}
