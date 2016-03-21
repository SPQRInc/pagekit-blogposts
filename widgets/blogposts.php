<?php

use Pagekit\Blog\Model\Post;

return [

	'name' => 'bixie/blogposts',

	'label' => 'Blog Posts',

	'events' => [

		'view.scripts' => function ($event, $scripts) use ($app) {
			$scripts->register('widget-blogposts', 'bixie/blogposts:app/bundle/widget-blogposts.js', ['~widgets']);
		}

	],

	'main' => function ($app) {
		$app['string.truncate'] = function ($text, $length = 200) {
			$text = strip_tags($text);
			if ($length) {
				if (function_exists('mb_strpos')) {
					if (($pos = @mb_strpos($text, ' ', $length)) > 0) {
						$text = mb_substr($text, 0, $pos) . '...';
					}
				} else {
					if (($pos = @strpos($text, ' ', $length)) > 0) {
						$text = substr($text, 0, $pos) . '...';
					}
				}
			}
			return $text;
		};
	},

	'render' => function ($widget) use ($app) {

		$posts = Post::where(
			['status = ?', 'date < ?'],
			[Post::STATUS_PUBLISHED, new \DateTime])->where(function ($query) use ($app) {
			return $query->where('roles IS NULL')->whereInSet('roles', $app['user']->roles, false, 'OR');
		})->related('user')->limit($widget->get('count'))->orderBy('date', 'DESC')->get();

		if ($posts) {
			foreach ($posts as $post) {
				$post->excerpt = $app['content']->applyPlugins($post->excerpt, ['post' => $post, 'markdown' => true]);
				$post->content = $app['content']->applyPlugins($post->content, ['post' => $post, 'markdown' => true]);
			}
		}
		return $app['view']('bixie/blogposts/widgets/blogposts.php', compact('widget', 'posts'));

	}

];
