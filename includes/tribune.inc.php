<?php

class Tribune {
	function conversation_json($id) {
		$answers = array();

		$post = Post::get($id);

		if (!$post) {
			return json_encode(array(
				'error' => 'Post not found',
			), JSON_PRETTY_PRINT);
		}

		$flat_posts = array(
			$post->id => $post,
		);

		$level = 0;
		$post->level = $level;
		while (count($flat_posts) < 25 && $level < 6) {
			foreach ($flat_posts as $post) {
				if ($post->level == $level-1) {
					foreach ($post->answers() as $answer) {
						$answer->level = $level;
						$flat_posts[$answer->id] = $answer;
					}
				}
			}

			$level++;
		}

		$array = array();
		foreach ($flat_posts as $post) {
			$array[] = $post->to_array();
		}

		return json_encode($array, JSON_PRETTY_PRINT);
	}
}

