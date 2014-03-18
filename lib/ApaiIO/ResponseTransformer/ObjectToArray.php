<?php
/*
 * Copyright 2013 Jan Eichhorn <exeu65@googlemail.com>
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace ApaiIO\ResponseTransformer;

/**
 * A responsetransformer transforming an object to an array
 *
 * @author Jan Eichhorn <exeu65@googlemail.com>
 */
class ObjectToArray implements ResponseTransformerInterface
{
    /**
     * {@inheritdoc}
     */
    public function transform($response)
    {
        return $this->buildArray($response);
    }

    /**
     * Transforms the responseobject to an array
     *
     * @param object $object
     *
     * @return array An arrayrepresentation of the given object
     */
    protected function buildArray($object)
    {
        $out = array();
        foreach ($object as $key => $value) {
            switch (true) {
                case is_object($value):
                    $out[$key] = $this->buildArray($value);
                    break;
                case is_array($value):
                    $out[$key] = $this->buildArray($value);
                    break;
                default:
                    $out[$key] = $value;
                    break;
            }
        }

        return $out;
    }

    /**
     * Cleans response from CSS, JS, and other embedded meta information
     * 
     * @param string $text
     * @return string
     */
    function html2txt($text)
    {
        $text = preg_replace( '/(<div id="variations-test">.+?)+(<\/div>)/i', '', $text );
        $text = preg_replace( '/(<style>.+?)+(<\/style>)/i', '', $text );
        $search = array('@<script[^>]*?>.*?</script>@si',  // Strip out javascript
                       '@<[\/\!]*?[^<>]*?>@si',            // Strip out HTML tags
                       '@<style[^>]*?>.*?</style>@siU',    // Strip style tags properly
                       '@<![\s\S]*?--[ \t\n\r]*>@'         // Strip multi-line comments including CDATA
        );
        $text = preg_replace( $search, '', $text );
        return trim( strip_tags( $text ) );
    }
}
