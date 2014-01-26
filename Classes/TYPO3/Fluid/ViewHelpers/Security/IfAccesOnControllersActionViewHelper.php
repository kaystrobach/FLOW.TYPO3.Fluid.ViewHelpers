<?php
namespace TYPO3\Fluid\ViewHelpers\Security;

use TYPO3\Flow\Aop\JoinPoint;


/*                                                                        *
 * This script belongs to the TYPO3 Flow package "Fluid".                 *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License, either version 3   *
 * of the License, or (at your option) any later version.                 *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */


/**
 * This view helper implements an ifAccess/else condition.
 *
 * ...
 *
 *
 *
 * @api
 */
class IfAccesOnControllersActionViewHelper extends \TYPO3\Fluid\Core\ViewHelper\AbstractConditionViewHelper {

	/**
	 * @var \TYPO3\Flow\Mvc\ActionRequest
	 */
	protected $request;

	/**
	 * Injects the access decision manager
	 *
	 * @Flow/Inject
	 * @var \TYPO3\Flow\Security\Authorization\AccessDecisionManagerInterface
	 */
	protected $accessDecisionManager;

	/**
	 * @Flow\Inject
	 * @var \TYPO3\Flow\Security\Authorization\AccessDecisionVoterManager
	 */
	protected $accessDecisionVoterManager;

	/**
	 * @Flow\Inject
	 * @var \TYPO3\Flow\Package\PackageManagerInterface
	 */
	protected $packageManager;

	/**
	 * initializes the needed properties of this viewHelper
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
		if($package === NULL) {
			$package = $this->request->getControllerPackageKey();
		}
		if(($package === NULL) && ($subpackage === NULL)) {
			$subpackage = $this->request->getControllerSubpackageKey();
		}
		if($controller === NULL) {
			$controller = $this->request->getControllerName();
		}
		if ($this->hasAccessToResource($package, $subpackage, $controller, $action)) {
			return $this->renderThenChild();
		} else {
			return $this->renderElseChild();
		}
	}

	/**
	 * Check if we currently have access to the given resource
	 *
	 * @param $packageKey
	 * @param $subpackageName
	 * @param $controllerName
	 * @param $actionName
	 * @return boolean TRUE if we currently have access to the given resource
	 */
	protected function hasAccessToResource($packageKey, $subpackageName, $controllerName, $actionName) {
		$namespace = $this->packageManager->getPackage($packageKey)->getNamespace();
		$className = $namespace . '\\Controller\\' . $controllerName . 'Controller';
		try {
			$this->accessDecisionVoterManager->decideOnJoinPoint(
				new JoinPoint(
					$this->objectManager->get($className),
					$className,
					$actionName,
					array()
				)
			);
		} catch(\TYPO3\Flow\Security\Exception\AccessDeniedException $e) {
			return FALSE;
		}
		return TRUE;
	}
}

?>
