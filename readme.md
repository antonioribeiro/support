# Support

## Timer

A timer class that can be called static or dynamically.

Source code: [support/blob/master/src/Timer.php](https://github.com/antonioribeiro/support/blob/master/src/Timer.php)

### Methods

Those are the methods:

	Timer::start();
	Timer::stop();
	Timer::isStarted();
	Timer::isStopped();
	Timer::elapsed(); // returns a formatted value 9.0192
	Timer::elapsedRaw(); // returns a double 9.019223049023
	Timer::setFormat(default = '%.4f');

You can name your timers and have more than one running:

	Timer::start('mary');
	Timer::stop('mary');
	Timer::elapsed('mary');

### Examples

	Timer::start();
	Timer::start('2nd timer');

	var_dump("started: " . (Timer::isStarted() ? 'yes' : 'no'));
	var_dump("stopped: " . (Timer::isStopped() ? 'yes' : 'no'));

	sleep(5);

	Timer::stop();

	var_dump("started: " . (Timer::isStarted() ? 'yes' : 'no'));
	var_dump("stopped: " . (Timer::isStopped() ? 'yes' : 'no'));
	var_dump("elapsed: " . Timer::elapsed());
	var_dump("raw: " . Timer::elapsedRaw());

	sleep(2);

	var_dump("'2nd timer' started: " . (Timer::isStarted('2nd timer') ? 'yes' : 'no'));
	var_dump("'2nd timer' stopped: " . (Timer::isStopped('2nd timer') ? 'yes' : 'no'));
	var_dump("'2nd timer' elapsed: " . Timer::elapsed('2nd timer'));
	var_dump("'2nd timer' raw: " . Timer::elapsedRaw('2nd timer'));

	sleep(2);

	Timer::stop('2nd timer');

	var_dump("'2nd timer' started: " . (Timer::isStarted('2nd timer') ? 'yes' : 'no'));
	var_dump("'2nd timer' stopped: " . (Timer::isStopped('2nd timer') ? 'yes' : 'no'));
	var_dump("'2nd timer' elapsed: " . Timer::elapsed('2nd timer'));
	var_dump("'2nd timer' raw: " . Timer::elapsedRaw('2nd timer'));

	Timer::setFormat('%.8f');
	var_dump("'2nd timer' elapsed 8 decimals: " . Timer::elapsed('2nd timer'));

/// And you can instantiate it and do it all over again:

	$t = new Timer;
	$t->start();
	sleep(3);
	$t->stop();
	var_dump("elapsed dynamic: " . $t->elapsed());

This should give you this result:

	string(12) "started: yes"
	string(11) "stopped: no"
	string(11) "started: no"
	string(12) "stopped: yes"
	string(15) "elapsed: 5.0004"
	string(20) "raw: 5.0005040168762"
	string(24) "'2nd timer' started: yes"
	string(23) "'2nd timer' stopped: no"
	string(27) "'2nd timer' elapsed: 7.0008"
	string(32) "'2nd timer' raw: 7.0008120536804"
	string(23) "'2nd timer' started: no"
	string(24) "'2nd timer' stopped: yes"
	string(27) "'2nd timer' elapsed: 9.0011"
	string(32) "'2nd timer' raw: 9.0010931491852"
	string(42) "'2nd timer' elapsed 8 decimals: 9.00113106"
	string(27) "elapsed dynamic: 3.00018883"
