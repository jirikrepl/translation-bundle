JMSTranslationBundle [![Build Status](https://secure.travis-ci.org/schmittjoh/JMSTranslationBundle.png?branch=master)](http://travis-ci.org/schmittjoh/JMSTranslationBundle)
====================

Documentation: 
[Resources/doc](http://jmsyst.com/bundles/JMSTranslationBundle)
    

Code License:
[Resources/meta/LICENSE](https://github.com/schmittjoh/JMSTranslationBundle/blob/master/Resources/meta/LICENSE)


Documentation License:
[Resources/doc/LICENSE](https://github.com/schmittjoh/JMSTranslationBundle/blob/master/Resources/doc/LICENSE)

With this forked bundle you can extract messages from twig variables. If you want to translate messages from variables, 
you have to use 'trans' prefix in variable name. If variable is associative array, use 'trans' prefix also for its keys. 

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

### working with trans filter
No domain (translation file) is set, defauld domain `messages` will be used.

```html+django
{{ 'some.key'|trans|desc('some trans') }}       
```

Set domain to `"newDomain"`.

```html+django
{{ 'another.key'|trans({}, "newDomain")|desc('some trans') }}
```

**globally set domain for twig file**

Note that this only influences the current template, not any "included" template (in order to avoid side effects).
It will not work also inside `{% embed %}` block. How to set domain for whole file is discussed below.

```html+django
{% trans_default_domain "app" %}
```

Read on to how to extract and display these variable messages.

## JMStranslation workflow ##
Jms file extractors works like this. Visit project directories and its contents (directories are defined trough extraction
command line command). If file have known extension (.twig, .php) for file extractor (TranslationBundle/Translation/Extractor/FileExtractor.php). 
Use appropriate extractor. All file extractors classes are in TranslationBundle/Translation/Extractor/File/

### Directory structure ###

To understand project extraction/tranlation workflow consider this symfony directory structure. This is structure is used
in [King's Calculator](http://kingscalculator.com/). We will have many simple calculator pages in one base layout.
We want to have one message domain (translation file) for every single calculator page.

<!-- language:console -->
    CalcBundle/
        Resources/
                translations/
                    DateCalculators/
                        ageCalc.cz.xliff
                        ageCalc.en.xliff
                        zodiacCalc.cz.xliff
                        zodiacCalc.en.xliff
                        ...
                    FitnessCalculators/
                        bmi.cz.xliff
                        ...
                views/
                    DateCalculators/
                        ageCalc.html.twig
                        zodiacCalc.html.twig
                        ...
                    FitnessCalculators/
                        bmi.html.twig
                        ...
        Tests/
        ...
### Convention for json files ###
To extract messages from json files, json files has to have 'Trans' substring in theirs names. E.g.: menuItemsTrans.json 

### Convention for twig files ###
Save translation domain into variable and use this variable BEFORE any variable with trans prefix.

```html+django
{# zodiacCalc.html.twig #}

{% extends 'RoyalBaseBundle::pageLayout.html.twig' %}

{# set message domain for this file #}
{% set dom = 'zodiacCalc' %}
{% set transVars = {'transPageTitle': 'Zodiac calculator', ... %}

{% block info %}
    {# in unforked jmsBundle domain name has to be scalar sting #}
    {# now the domain name can be referenced by variable name (in this case: dom) #}
    {{ 'zodiac.info'|trans({}, dom)|desc('Enter your birthdate...') }}
{% endblock %}
```

Common page layout is used for every calculator.

```html+django
{# pageLayout.html.twig #}

...
{% block title %}
    {# content of dom variable comes from extended zodiacCalc.html.twig #} 
    {{ transVars.transPageTitle|trans({}, dom) }}
{% endblock %}
...
```

### Save common extraction config ###

In your config_dev.yaml you can save jms_traslation configs. This simplifies extraction command line commands. List another 
settings options with command: `php bin\console translation:extract --help` 

<!-- language:console -->
    parameters:
        calc.trans_dir: %kernel.root_dir%/../src/Royal/CalcBundle/Resources/translations
        views_dir: %kernel.root_dir%/../src/Royal/CalcBundle/Resources/views
        
    jms_translation:
        configs:
            bmi:
                dirs: [%views_dir%]
                output_dir: %calc.trans_dir%/DateCalculators
                domain: bmi
            zodiacCalc:
                dirs: [%views_dir%]
                output_dir: %calc.trans_dir%/DateCalculators
                domain: zodiacCalc
            ageCalc:
                dirs: [%views_dir%]
                output_dir: %calc.trans_dir%/DateCalculators
                domain: ageCalc
       
### extraction commands ###
                
To extract messages using predefined commands above use --config option (alias -c):
                
<!-- language:console -->
    php app\console translation:extract cz -c bmi
    php app\console translation:extract cz -c zodiacCalc
    php app\console translation:extract cz -c ageCalc
    
### extracted messages file
When you extract messages for ZodiacCalc with this command:
 
 `php bin\console translation:extract cz -c zodiacCalc`
 
JmsBundle will create xliff file stored in location defined in config above. So the location would be:

 `src/Royal/CalcBundle/Resources/translations/DateCalculators/zodiacCalc.cz.xliff`

This is the body element of zodiacCalc.cz.xliff file:

```xml
<body>
  <trans-unit id="8df97dd459b6301b92ca8bd230052bce90c46d12" resname="Zodiac calculator">
      <jms:reference-file line="6">views/DateCalculators/zodiac.html.twig</jms:reference-file>
      <source>Zodiac calculator</source>
      <target state="new">Zodiac calculator</target>
  </trans-unit>
  <trans-unit id="e27d952ab13d50d89484b3cb47530059f82bb30b" resname="zodiac.info">
      <jms:reference-file line="13">views/DateCalculators/zodiac.html.twig</jms:reference-file>
      <source>Enter your birthdate and discover what your zodiac sign is.</source>
      <target state="new">Zadejte datum narození a zjistěte...</target>
  </trans-unit>
</body>
```
    
### other useful notes
When you are not using automated extraction and manually adding some translations to your to your xliff files. 
And those manually added translations does not have keys in twig file.
 e.g.: Key `'someKey'` exists only in xliff file and not in twig file.
 
 
 If this code with `someKey` would exist in twig file, it would be automatically extracted into xliff
 ```html+django
     {{ 'someKey'|trans({}, dom)|desc('Enter your birthdate...') }}
 ```
 
 ```xml
 <body>
   <trans-unit id="8df97dd459b6301b92ca8bd230052bce90c46d12" resname="someKey">
       <jms:reference-file line="6">views/DateCalculators/zodiac.html.twig</jms:reference-file>
       <source>I am manually added tranlation</source>
       <target state="new">Jsem manuálně přidáný překlad</target>
   </trans-unit>
 </body>
 ```
 
Then you lost your manual translations if you don't add `--keep` option in command: 

```
php bin/console translation:extract cz --bundle=RoyalBaseBundle --keep
```

To know if some messages will be deleted and some added use this command   

```
php bin/console translation:extract cz --bundle=RoyalBaseBundle --dry-run
```
