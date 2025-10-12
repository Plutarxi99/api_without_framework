<?php

/**
 * Простой класс для построения роутов
 */
class Router
{
    private array $routes = [];

    /**
     * Добавить маршрут
     *
     * @param string   $method  Метод
     * @param string   $pattern регулярка без обрамления
     * @param callable $handler callable
     *
     * @return void
     */
    public function add(string $method, string $pattern, callable $handler): void
    {
        $this->routes[] = [strtoupper($method), $pattern, $handler];
    }

    /**
     * Диспетчер — пройдёт по маршрутам и вызовет handler.
     *
     * @param string $uri    должен быть только path
     * @param string $method метод, который вызовется
     *
     * @return void
     */
    public function dispatch(string $method, string $uri): void
    {
        $method = strtoupper($method);

        foreach ($this->routes as [$m, $pattern, $handler]) {
            if ($m !== $method) {
                continue;
            }

            if (! preg_match('#^' . trim($pattern, '#') . '$#', $uri, $matches)) {
                continue;
            }

            array_shift($matches); // убрать полный матч
            // вызываем handler с параметрами
            call_user_func_array($handler, $matches);
            return;
        }

        // если ничего не подошло
        respond_json(['error' => 'Not found'], 404);
    }
}
