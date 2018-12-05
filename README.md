Этот концепт будет интересен тем, кто разрабатывает приложения с большим количеством ендпоинтов. Не важно что это за приложение: web, cli, обработчик задач из очереди или какой-либо другой сервис. Рано или поздно в подобных проектах возникает потребность запускать ендпоинты в разных контекстах. Т.е. один и тот же код должен иметь возможность быть запущен как через web, cli, queue или какими-то другими способами.

Проблема заключается в том, что сейчас нет удобного решения, по крайней мере я не смог найти, которое бы позволяло разрабатывсать подобные приложения без дублирования кода. На данный момент наиболее близким решением явлояется использование CQRS (Command Query Responsibility Segregation) подхода. Опять же мне пока не уцдалось полноценно изучить этот подход и попробовать его на практике, поэтому какие-то из выводов могут быть не верны.

CQRS – подход проектирования ПО требующий отделять код изменяющий состояние от кода читающего состояние. На первый взгляд может показаться не очень то и понятно. В основе этого подхода лежит другой принцип — CQS (Command-query separation).

Идея CQS заключается в том, что методы объектов могут быть двух типов:
- Queries — возвращают результат не изменяя состояние объекта. У Query не никаких побочных эффектов.
- Commands — изменяют состояние объекта, не возвращая значение.

Главное отличие CQS от CQRS, в том, что второй подразумевает разделение типов методов по разным классам. Для изменения состояния создается Command-класс, а для выборки данных — Query-класс. CQRS требует явно выделять классы Command и Query.


В самом простом случае команда представляет из себя подобный класс:

```
class SimpleCommand {
    public $name;
    public $description;
}
```


Затем нужно реализровать обработчик команды:

```
class SimpleCommandHandler {
    protected $validator;

    public function __construct(ValidatorInterface $validator) {
        $this->validator = $validator;
    }

    public function handle(SimpleCommand $command) {
        $violations = $this->validator->validate($command);

        if (count($violations) != 0) {
            $error = $violations->get(0)->getMessage();
            throw new BadRequestHttpException($error);
        }

        echo $command->name;
        echo $command->description;

        // something
    }
}
```

А выполняется данный код так:

```
$command = new SimpleCommand();
$command->name        = $name;
$command->description = $description;

$this->container->get('command_bus')->handle($command);
```

Таким образом код выполняющий полезное действие может быть выполнен в любом контексте запуска при условии что ему будет передана корректно запоненная команда. Разработчику остаётся только реализовать заполнение каждой команды для каждого контекста. Вот тут и кроется самое неприятное. Представьте, у вас есть 100 команд и у вас появляется новый контекст для запуска.

Например, все 100 ендпоинтов  веб-приложения нужно сделать доступными для запуска в виде консольного приложения. Разработчику ничего не остаётся, как монотонно, нудно и богомерзско напечатать 100 заполнений команд полчая данные из командной строки. Ужасно, не правда ли? Затем, в последствии эти же 100 ендпоинтов нужно уметь запускать по мере поступления задач через систему очередей. Окей, программист снова напечатает ещё 100 едениц кода, заполнящего команды из данных пришедших через очередь.

Итого получилось 300 мест в которых происмходит заполнение данных для обработчиков. По 3 на каждый обработчик. Добавляется новый обработчик - нужно пилить 3 варианта заполнения команды. Меняется набор полей команды - нужно изменить код как минимум в трёх местах. Происходит что-то типа комбинаторного взрыва, что не может являться хорошей архитектурой. Заниматься поддержкой такой системы - нудная и монотонная задача.

Согласитесь, было бы напного удобнее, чтобы команда умела автозаполняться вне зависимости от контекста, в котором запущено приложения. Тогда бы разработчику не пришлось писать 300 раз код заполнячющий поля команды.


Для каждого контекста придётся писать много однотипного кода.


```
$command = new SimpleCommand();
$command->name        = $_GET['name'];
$command->description = $_GET['description'];
```

```
$command = new SimpleCommand();
$command->name        = $_POST['name'];
$command->description = $_POST['description'];
```


```
$command = new SimpleCommand();
$command->name        = $args[1];
$command->description = $args[2];
```


```
$command = new SimpleCommand();
$command->name        = $request->query->get('name');
$command->description = $request->query->get('description');
```


```
$command = new SimpleCommand();
$command->name        = $input->getArgument('name');
$command->description = $input->getOption('description');
```


```
$command = new SimpleCommand();
$command->name        = $data['name'];
$command->description = $data['description'];
```

Но было бы круто, если бы не нужно было заполнять вручную объект с командой для каждого контекста. Только представьте, если бы команда могла сама получать нужныке данные в зависимости от контекста запуска.


Как я себе это представляю? В коде обработчика это мсодет выглядеть примерно так:

```
class SimpleCommandHandler {

    public function handle(SimpleCommand $command) {
        echo $command->get('name');
        echo $command->get('description');
    }

}
```

Или так:

```
class SimpleCommandHandler {

    public function handle(SimpleCommand $command) {
        echo $command->getName();
        echo $command->getDescription();
    }

}
```

Сама же команда может выглядеть так:

```
class SimpleCommand extemds ManyInputCommand {

    /**
    * @ManyInput\HttpGet('name')
    * @ManyInput\CliArg(1)
    * @ManyInput\InputArg('name')
    * @ManyInput\Array(0)
    * @ManyInput\Json('$.data.name')
    **/
    public $name;

    /**
    * @ManyInput\HttpPost('description')
    * @ManyInput\HttpPost('descr')
    * @ManyInput\CliArg(2)
    * @ManyInput\InputOption('description')
    * @ManyInput\Array(1)
    * @ManyInput\Json('$.data.options.description')
    **/
    public $description;
}
```
И в зависимости от контекста запуска ManyInputCommand будет вытаскивать нужное значение из нужного контекста. Таким образом, привязка команды к разным контекстам запуска будет находиться в одной точке, а не будет разбросана по всему коду.
