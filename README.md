JMSTranslationBundle [![Build Status](https://secure.travis-ci.org/schmittjoh/JMSTranslationBundle.png?branch=master)](http://travis-ci.org/schmittjoh/JMSTranslationBundle)
====================

Documentation: 
[Resources/doc](http://jmsyst.com/bundles/JMSTranslationBundle)
    

Code License:
[Resources/meta/LICENSE](https://github.com/schmittjoh/JMSTranslationBundle/blob/master/Resources/meta/LICENSE)


Documentation License:
[Resources/doc/LICENSE](https://github.com/schmittjoh/JMSTranslationBundle/blob/master/Resources/doc/LICENSE)
=======

With this forked bundle you can extract messages from variables. If you want to translate messages form variables, 
you have to use 'trans' prefix in its name. If variable is associative array, use 'trans' prefix for its keys. 

```html+django
{# only value with 'transPageTitle will be extracted' #}
{% set transArray = {'transPageTitle': 'someTitle', 'someKey': 'ignoreMe'} %}

{# scalar variable with trans prefix #}
{% set transVar = 'someTransMessage'%}

{# all items of array will be extracted #}
{% set transArray = ['one', 'two', 'three']%}

{# ignore this variable for extraction #}
{% set ignoreMe = 'ignoreThis' %}
```