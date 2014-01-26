<?php
namespace TYPO3\Fluid\ViewHelpers\Security;

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
	 * Injects the access decision manager
	 *
	 * @Flow/Inject
	 * @var \TYPO3\Flow\Security\Authorization\AccessDecisionManagerInterface
	 */
	protected $accessDecisionManager;

	/**
	 * @Flow\Inject
	 * @var \TYPO3\Flow\Package\PackageManagerInterface
	 */
	protected $packageManager;

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
	public function render($package = NULL, $subpackage = NULL, $controller = NULL, $action) {
		if($package === NULL) {
			$package = $this->controllerContext->getUriBuilder()->getRequest()->getControllerPackageKey();
		}
		if(($package === NULL) && ($subpackage === NULL)) {
			$subpackage = $this->controllerContext->getUriBuilder()->getRequest()->getControllerSubpackageKey();
		}
		if($controller === NULL) {
			$controller = $this->controllerContext->getUriBuilder()->getRequest()->getControllerName();
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
	 * @param $package
	 * @param $subpackage
	 * @param $controller
	 * @param $action
	 * @return boolean TRUE if we currently have access to the given resource
	 */
	protected function hasAccessToResource($package, $subpackage, $controller, $action) {
		$namespace = $this->packageManager->getPackage($package)->getNamespace();
		$className = $namespace . '\\Controller\\' . $controller . 'Controller';
		try {
			$this->accessDecisionVoterManager->decideOnJoinPoint(
				new JoinPoint(
					$this->objectManager->get($className),
					$className,
					$action,
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
