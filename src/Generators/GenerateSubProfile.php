<?php

namespace Drupal\ezcontent\Generators;

use DrupalCodeGenerator\Command\BaseGenerator;
use DrupalCodeGenerator\Utils;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

/**
 * Implements ezcontent-subprofile command.
 */
class GenerateSubProfile extends BaseGenerator {

  /**
   * The command name.
   *
   * @var string
   */
  protected $name = 'ezcontent-sub-profile';

  /**
   * The command description.
   *
   * @var string
   */
  protected $description = 'Command to generates a sub profile';

  /**
   * The command alias.
   *
   * @var string
   */
  protected $alias = 'ezcontent-sub-profile';

  /**
   * The destination.
   *
   * @var mixed
   */
  protected $destination = 'profiles/custom/%';

  /**
   * A path where templates are stored.
   *
   * @var string
   */
  protected $templatePath = __DIR__ . '/templates';

  /**
   * {@inheritdoc}
   */
  protected function interact(InputInterface $input, OutputInterface $output) {
    $profile = \Drupal::service('config.factory')
      ->get('core.extension')
      ->get('profile');
    if ($profile != 'ezcontent') {
      throw new \UnexpectedValueException('Please make sure ezcontent profile is installed.');
    }
    $questions = Utils::moduleQuestions();
    $questions['name'] = new Question('Enter sub profile name', 'EZcontent sub profile');
    $questions['machine_name'] = new Question('Enter sub profile machine name', 'ezcontent_subprofile');
    $questions['themes'] = new Question('Themes to install (comma separated)');
    $questions['install'] = new Question('Additional module to install(comma separated)');
    $questions['exclude'] = new Question('Module to exclude (comma separated)');
    $subprofilePath = '/profiles/custom/{machine_name}';
    $this->addFile($subprofilePath . '/{machine_name}.info.yml')
      ->template('subprofile-info.twig');
    $this->addFile($subprofilePath . '/{machine_name}.module')
      ->template('subprofile-module.twig');
    $this->addFile($subprofilePath . '/{machine_name}.install')
      ->template('subprofile-install.twig');
  }

}
