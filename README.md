## Contents Of This File

 * About EzContent
 * Key features
 * Drupal 8 Installation
 * Drupal 9 Installation
 * Recommendation

## About EzContent

[EzContent](https://www.drupal.org/project/ezcontent) is a Drupal
 installation profile that addresses common content management 
 pain points and accelerates CMS implementations.

 <img src="https://www.drupal.org/files/project-images/EzContent_0.jpg"
 width="300px" height="300px"/>


## Key Features

   -  ### Rich Component Library:
       Rich library of commonly used components, 
       available both as paragraphs and custom block types.
  
   - ### SEO-friendly content creation:
      SEO friendly modules such as real-time on-page SEO, metatags,
      schema.org, XML sitemap, and automatic url alias are pre-configured
      to increase your content visibility on the web from Day 1.
      Common SEO features are logically grouped for an enhanced
      editorial interface.

  

   - ### Decoupled support don't comprise on nonnegotiable CMS features:
        When used in a decoupled setup, EzContent allows enterprises
        to painlessly create interactive UIs with the choice of their
        frontend frameworks without whittling away non-negotiable
        features such as:
     
        1) Preview unpublished content in de-coupled front-ends
        2) Use Drupal's layout builder to create landing pages
           with de-coupled front-ends.
        If you want to use EzContent as a pure de-coupled implementation,
        see <a href="https://www.drupal.org/project/ezcontent_api">
        EzContent API</a> for more details and links to Angular,
        Gatsby and Nextjs starter kits.
   
  

  - ### AI-generated content:
    Powered by Srijan AI services and Amazon Rekognition,
    some of the capabilities include AI-based image captioning,
    image tagging, smart content tagging and automatic text generation tools.

## Drupal 8 Installation
EzContent can be installed in two ways:
### Via Drupal Composer
- Choose a name for your project, like “MY_PROJECT”
- Use the given command to create the project
- The command will download Drupal core along with necessary modules, 
  EzContent profile, and all other dependencies necessary for the project

```bash 
composer create-project srijanone/ezcontent-project
MY_PROJECT --no-interaction 
```

In case you come across any memory issues, run this command -

```bash 
php -d memory_limit=-1 /path/to/composer.phar create-project 
srijanone/ezcontent-project MY_PROJECT --no-interaction 
```

### Via Acquia BLT
To create a new Acquia BLT project using Ez content,
use the following commands -

```bash 
composer create-project --no-interaction acquia/blt-project MY_PROJECT;
cd MY_PROJECT;
composer require srijanone/ezcontent:^1.0;
```

#### Warning: 
There may be updates to BLT, Lightning which may break the setup.
If you see any issue, please open a new issue in the issue queue.

## Drupal 9 Installation

### Via Drupal Composer

```bash
composer create-project srijanone/ezcontent-project:9.x-dev
MY_PROJECT --no-interaction 
```

### Via Acquia BLT

```bash
composer create-project --no-interaction acquia/drupal-recommended-project
MY_PROJECT;
cd MY_PROJECT;
composer require srijanone/ezcontent:9.x-dev;
```

### Demo Setup

Please use the following single command installer to setup a demo site. Please
refer <a href="https://www.drupal.org/project/ezcontent_demo" target="_blank">
EzContent Demo</a> package to know more about this.

```bash
COMPOSER_MEMORY_LIMIT=-1 composer create-project 
srijanone/ezcontent-project:dev-demo ezcontent-demo --no-interaction;
```
Currently ezcontent_demo module is a bit heavy which requires extra memory to
complete its installation via installation user interface, we recommend you to
install this module via drush or try installing its sub module one by one to
distribute the load.
Drush command :

```bash
drush en -y ezcontent_demo
```
