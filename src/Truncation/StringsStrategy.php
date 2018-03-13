<?php namespace Rollbar\Truncation;

use \Rollbar\Payload\EncodedPayload;

class StringsStrategy extends AbstractStrategy
{
    
    public static function getThresholds()
    {
        return array(1024, 512, 256);
    }
    
    public function execute(EncodedPayload $payload)
    {
        $data = $payload->data();
        
        $modified = false;
        
        foreach (static::getThresholds() as $threshold) {
            if (!$this->truncation->needsTruncating($payload, $this)) {
                break;
            }
            
            array_walk_recursive($data, function (&$value) use ($threshold, &$modified, $payload) {
                
                if (is_string($value) && $strlen = strlen($value) > $threshold) {
                    $value = substr($value, 0, $threshold);
                    $modified = true;
                    $payload->decreaseSize($strlen - $threshold);
                }
            });
        }
        
        if ($modified) {
            $payload = new EncodedPayload($data);
            $payload->encode();
        }
        
        return $payload;
    }
}
