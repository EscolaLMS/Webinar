<?php

namespace EscolaLms\Webinar\Helpers;

class StrategyHelper
{
    private string $namespace;

    public function __construct(string $baseStrategyName)
    {
        $this->setNamespace($baseStrategyName);
    }

    /**
     * This method used strategy pattern and execute method given in the parameters
     * Strategy dir it must contain minimum to file: BaseStrategy contain in pattern {{parentDir}}Strategy
     * in localization ?/Strategies/{{parentDir}} and strategy class in the same localization
     *
     * @param string $className
     * @param string $baseStrategyName
     * @param string $method
     * @param ...$params
     * @return mixed|null
     */
    public static function useStrategyPattern(
        string $className,
        string $baseStrategyName,
        string $method,
        ...$params
    ) {
        $strategyHelper = new StrategyHelper($baseStrategyName);
        $class = $strategyHelper->namespace . '\\' . $className;
        $baseStrategyClass = $strategyHelper->namespace . '\\' . $baseStrategyName;
        if (
            class_exists($class) &&
            class_exists($baseStrategyClass) &&
            method_exists($baseStrategyClass, $method)
        ) {
            $strategy = new $baseStrategyClass(
                new $class($params)
            );

            return $strategy->$method();
        }
        return null;
    }

    private function setNamespace(string $baseStrategyName): void
    {
        $this->namespace = 'EscolaLms\Webinar\Strategies\\' .
            preg_replace('/^(.*)Strategy$/', '$1', $baseStrategyName);
    }
}
