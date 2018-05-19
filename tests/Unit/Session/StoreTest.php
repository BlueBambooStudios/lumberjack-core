<?php

namespace Rareloop\Lumberjack\Test;

use Mockery;
use PHPUnit\Framework\TestCase;
use Rareloop\Lumberjack\Session\Store;
use Rareloop\Lumberjack\Test\Unit\Session\NullSessionHandler;

class SessionTest extends TestCase
{
    use \Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

    /** @test */
    public function can_get_session_name()
    {
        $store = new Store('session-name', new NullSessionHandler, 'session-id');

        $this->assertSame('session-name', $store->getName());
    }

    /** @test */
    public function can_get_session_id()
    {
        $store = new Store('session-name', new NullSessionHandler, 'session-id');

        $this->assertSame('session-id', $store->getId());
    }

    /** @test */
    public function can_put_a_single_key_value_pair()
    {
        $store = new Store('session-name', new NullSessionHandler, 'session-id');

        $store->put('foo', 'bar');

        $this->assertSame('bar', $store->get('foo'));
    }

    /** @test */
    public function can_get_a_default_value_if_none_is_present()
    {
        $store = new Store('session-name', new NullSessionHandler, 'session-id');

        $this->assertSame(null, $store->get('foo'));
        $this->assertSame('bar', $store->get('foo', 'bar'));
    }

    /** @test */
    public function can_get_all_values()
    {
        $store = new Store('session-name', new NullSessionHandler, 'session-id');

        $store->put('foo1', 'bar1');
        $store->put('foo2', 'bar2');

        $this->assertSame([
            'foo1' => 'bar1',
            'foo2' => 'bar2',
        ], $store->all());
    }

    /** @test */
    public function can_check_if_session_has_a_value_set()
    {
        $store = new Store('session-name', new NullSessionHandler, 'session-id');

        $store->put('foo', 'bar');

        $this->assertTrue($store->has('foo'));
        $this->assertFalse($store->has('foo1'));
    }

    /** @test */
    public function can_pull_a_value()
    {
        $store = new Store('session-name', new NullSessionHandler, 'session-id');

        $store->put('foo', 'bar');
        $this->assertTrue($store->has('foo'));

        $value = $store->pull('foo');
        $this->assertSame('bar', $value);

        $this->assertFalse($store->has('foo'));
    }

    /** @test */
    public function can_push_a_value_into_an_array_when_not_previously_set()
    {
        $store = new Store('session-name', new NullSessionHandler, 'session-id');

        $store->push('foo', 'bar');

        $this->assertSame(['bar'], $store->get('foo'));
    }

    /** @test */
    public function can_forget_a_single_value()
    {
        $store = new Store('session-name', new NullSessionHandler, 'session-id');

        $store->push('foo', 'bar');
        $store->forget('foo');

        $this->assertFalse($store->has('foo'));
    }

    /** @test */
    public function can_forget_multiple_values()
    {
        $store = new Store('session-name', new NullSessionHandler, 'session-id');

        $store->push('foo1', 'bar1');
        $store->push('foo2', 'bar2');
        $store->forget(['foo1', 'foo2']);

        $this->assertFalse($store->has('foo1'));
        $this->assertFalse($store->has('foo2'));
    }

    /** @test */
    public function starting_a_session_loads_data_from_handler()
    {
        $handler = Mockery::mock(NullSessionHandler::class.'[read]');
        $handler->shouldReceive('read')->once()->with('session-id')->andReturn(serialize(['foo' => 'bar']));

        $store = new Store('session-name', $handler, 'session-id');

        $store->start();

        $this->assertSame('bar', $store->get('foo'));
    }

    /** @test */
    public function saving_a_session_writes_data_to_handler()
    {
        $handler = Mockery::mock(NullSessionHandler::class.'[write]');
        $handler->shouldReceive('write')->once()->with('session-id', Mockery::on(function ($argument) {
            $array = @unserialize($argument);

            return isset($array['foo']) && $array['foo'] === 'bar';
        }));

        $store = new Store('session-name', $handler, 'session-id');

        $store->start();
        $store->put('foo', 'bar');
        $store->save();
    }

        /** @test */
    public function can_flash_a_value_into_the_session()
    {
        $store = new Store('session-name', new NullSessionHandler, 'session-id');

        $store->flash('foo', 'bar');

        $this->assertSame('bar', $store->get('foo'));
    }

    /** @test */
    public function can_read_flash_value_after_one_save()
    {
        $store = new Store('session-name', new NullSessionHandler, 'session-id');

        $store->flash('foo', 'bar');
        $store->save();

        $this->assertSame('bar', $store->get('foo'));
    }

    /** @test */
    public function can_not_read_flash_value_after_two_saves()
    {
        $store = new Store('session-name', new NullSessionHandler, 'session-id');

        $store->flash('foo', 'bar');
        $store->save();
        $store->save();

        $this->assertSame(null, $store->get('foo'));
    }
}