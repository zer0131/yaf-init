<?php

/**
 * @author zhangenrui
 * @desc Yaf初始化类
 */
class Bootstrap extends Yaf_Bootstrap_Abstract {
    public function _initErrorHandler() {
        Fx_ErrorHandler::registerErrorHandler();
    }

    public function _initRequest(Yaf_Dispatcher $dispatcher) {
        $request = $dispatcher->getRequest();
        define('IS_GET', $request->isGet() ? true : false);
        define('IS_POST', $request->isPost() ? true : false);
        define('IS_PUT', $request->isPut() ? true : false);
        define('IS_DELETE', $request->getMethod() == 'DELETE' ? true : false);
        define('IS_AJAX', $request->isXmlHttpRequest() ? true : false);
    }

    public function _initRoute(Yaf_Dispatcher $dispatcher) {
        //在这里注册自己的路由协议,默认使用static路由
        $router = $dispatcher->getRouter();
        $route = new Yaf_Route_Simple("__m", "__c", "qt");
        $router->addRoute("helloRouter", $route);
    }

    public function _initPlugin(Yaf_Dispatcher $dispatcher) {
        $testPlugin = new Plugin_Test();
        $dispatcher->registerPlugin($testPlugin);
    }

    public function _initView(Yaf_Dispatcher $dispatcher) {
        //在这里注册自己的view控制器
        $dispatcher->disableView();//禁止自动渲染模板
    }

    public function _initDefaultName(Yaf_Dispatcher $dispatcher) {
        $environment = Yaf_Application::app()->environ();
        if ($environment == 'product') {
            $dispatcher->catchException(true);
        } else {
            $dispatcher->catchException(false);
        }
        //设置路由默认信息
        $dispatcher->setDefaultModule('Index')->setDefaultController('Index')->setDefaultAction('index');
    }
}