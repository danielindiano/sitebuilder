<?php

require_once 'app/models/sites.php';
require_once 'app/models/users.php';

class CreateSiteController extends AppController
{
	protected $uses = array();
	protected $workflowSteps = array('theme', 'business_info');

	protected function beforeFilter()
	{
		if (!Auth::loggedIn()) {
			$this->redirect('/users/login');
		}

		if ($this->getCurrentSite()->userRole() != Users::ROLE_ADMIN) {
			Session::writeFlash('error', s('Sorry, you are not allowed to do this'));
			$this->redirect('/categories');
		}

		if ($session = Session::read('CreateSite')) {
			$currentStep = array_search($session['step'], $this->workflowSteps);
			$attemptedStep = array_search($this->param('action'), $this->workflowSteps);
			if ($currentStep < $attemptedStep) {
				$this->redirect("/create_site/{$session['step']}");
			}
		}
	}

	public function theme()
	{
		$session = Session::read('CreateSite');

		$site = new Sites(array('segment' => MeuMobi::segment()));

		if ($session && array_key_exists('site', $session)) {
			$site->updateAttributes($session['site']);
		}

		if (!empty($this->data)) {
			$site->updateAttributes($this->data);
			if ($site->validateTheme()) {
				Session::write('CreateSite', array(
					'step' => 'business_info',
					'site' => $site->data
				));
				$this->redirect('/create_site/business_info');
			}
		}

		$themes = Model::load('Themes')->all();

		$this->set(compact('site', 'themes'));
	}

	public function business_info()
	{
		$session = Session::read('CreateSite');

		$site = new Sites();

		$site->updateAttributes($session['site']);

		if (!empty($this->data)) {
			$site->updateAttributes($this->data);

			if ($site->validate()) {
				$site->save();
				Session::delete('CreateSite');
				Session::writeFlash('success', s('Congratulations! Your mobile site is ready!'));
				$this->redirect('/categories');
			}
		}

		if ($site->state_id) {
			$states = Model::load('States')->toListByCountryId($site->country_id, array(
				'order' => 'name ASC'
			));
		} else {
			$states = array();
		}

		$countries = Model::load('Countries')->toList(array(
			'order' => 'name ASC'
		));

		$this->set(compact('site', 'countries', 'states'));
	}
}
