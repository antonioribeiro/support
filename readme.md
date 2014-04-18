# Support

## Timer Examples

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

	Timer::setFormat("%.8f");
	var_dump("'2nd timer' elapsed 8 decimals: " . Timer::elapsed('2nd timer'));

/// And you can instantiate it and do it all over again:

	$t = new Timer;
	$t->start();
	sleep(3);
	$t->stop();
	var_dump("elapsed dynamic: " . $t->elapsed());

