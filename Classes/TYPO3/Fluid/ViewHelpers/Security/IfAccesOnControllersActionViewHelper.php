<?php

namespace TYPO3\Fluid\ViewHelpers\Security;

/* *
 * This script belongs to the TYPO3 Flow package "TYPO3.Fluid".           *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License, either version 3   *
 * of the License, or (at your option) any later version.                 *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */
use TYPO3\Flow\Annotations as Flow;
use		TYPO3\Flow\Security\Authorization\AccessDecisionManagerInterface,
		TYPO3\Flow\Security\Exception\AccessDeniedException,
		TYPO3\Fluid\Core\ViewHelper\AbstractConditionViewHelper,
		TYPO3\Flow\Aop\JoinPoint;

/**
 * This view helper implements an ifAccess/else condition.
 *
 * = Examples =
 *
 * <code title="Basic usage">
 * <f:security.ifAccesOnControllersAction>
 *   This is being shown in case you have access to the given action
 * < /f:security.ifAccesOnControllersAction>
 * </code>
 *
 * Everything inside the <f:security.ifAccesOnControllersAction> tag is being displayed if you have access to the given action.
 *
 * <code title="ifAccesOnControllersAction / then / else">
 * <f:security.ifAccesOnControllersAction action="myAction" controller="MyController" subpackage="Some\Subpackage">
 *   <f:then>
 *     This is being shown in case you have access.
 *   < /f:then>
 *   <f:else>
 *     This is being displayed in case you do not have access.
 *   < /f:else>
 * < /f:security.ifAccesOnControllersAction>
 * </code>
 *
 * Everything inside the "then" tag is displayed if you have access.
 * Otherwise, everything inside the "else"-tag is displayed.
 *
 * @api
 */
class IfAccesOnControllersActionViewHelper extends AbstractConditionViewHelper {

	/**
	 * @Flow\Inject
	 * @var \TYPO3\Flow\Security\Authorization\AccessDecisionManagerInterface
	 */
	protected $accessDecisionVoterManager;

	/**
	 * @Flow\Inject
	 * @var \TYPO3\Flow\Mvc\Routing\RouterInterface
	 */
	protected $router;

	/**
	 * @var \TYPO3\Flow\Mvc\ActionRequest
	 */
	protected $request;

	/**
	 * Initializes the view helper before invoking the render method.
	 *
	 * Override this method to solve tasks before the view helper content is rendered.
	 *
	 * @return void
	 */
	public function initialize() {
		parent::initialize();
		$this->request = $this->controllerContext->getRequest();
	}

	/**
	 * renders <f:then> child if access to the given resource is allowed, otherwise renders <f:else> child.
	 *
	 * @param null $package
	 * @param null $subpackage
	 * @param null $controller
	 * @param $action
	 * @internal param string $resource Policy resource
	 * @return string the rendered string
	 * @api
	 */
	public function render($action, $package = NULL, $subpackage = NULL, $controller = NULL) {
		if ($package === NULL) {
			$package = $this->request->getControllerPackageKey();
		}
		if (($package === NULL) && ($subpackage === NULL)) {
			$subpackage = $this->request->getControllerSubpackageKey();
		}
		if ($controller === NULL) {
			$controller = $this->request->getControllerName();
		}

		if ($this->hasAccessToAction($package, $subpackage, $controller, $action)) {
			return $this->renderThenChild();
		} else {
			return $this->renderElseChild();
		}
	}

	/**
	 * Check if we currently have access to the given resource
	 *
	 * @param $packageKey
	 * @param $subpackageKey
	 * @param $controllerName
	 * @param $actionName
	 * @return boolean TRUE if we currently have access to the given action
	 */
	protected function hasAccessToAction($packageKey, $subpackageKey, $controllerName, $actionName) {
		$actionControllerObjectName = $this->router->getControllerObjectName($packageKey, $subpackageKey, $controllerName);
		try {
			$this->accessDecisionVoterManager->decideOnJoinPoint(
				new JoinPoint(
					NULL,
					$actionControllerObjectName,
					$actionName . 'Action',
					array()
				));
		} catch(AccessDeniedException $e) {
			return FALSE;
		}
		return TRUE;
	}

}
