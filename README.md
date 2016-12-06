# OptimisticLock plugin for CakePHP

## Installation

You can install this plugin into your CakePHP application using [composer](http://getcomposer.org).

The recommended way to install composer packages is:

```
composer require kaihiro/optimist
```

## Usage

### Table

```
<?php
class PostsTable extends Table
{
    public function initialize(array $config)
    {
        $this->addBehavior('OptimisticLock.OptimisticLock');
	}
}
```

### FormHelper

```
<?php
use OptimisticLock\View\Helper\OptimisticLockFormTrait;
class AppFormHelper extends FormHelper
{
    use OptimisticLockFormTrait;
}
```

### Controller



```
<?php
use OptimisticLock\Exception\OptimisticLockException;
class PostsController extends AppController
{
    public function edit($id = null)
    {
        $post = $this->Posts->get($id);

        if ($this->request->is(['patch', 'post', 'put'])) {
            try {
            	$post = $this->Posts->patchEntity($post, $this->request->data);
                if ($this-> Posts->save($post)) {
                    $this->Flash->success(__('The post result has been saved.'));

                    return $this->redirect(['action' => 'index']);
                } else {
                    $this->Flash->error(__('The post could not be saved. Please, try again.'));
                }
            } catch (OptimisticLockException $e) {
                $this->Flash->error(__('optimistic lock error.'));
            }
        }
        $this->set(compact('post'));
        $this->set('_serialize', ['post']);
    }
```
