<?php

use Nextras\Orm\EntityCollection;

/**
 * @property string $title
 * @property Author $author
 * @property Author|NULL $translator
 *
 * @property-read int $id
 * @property-read int $author_id
 * @property-read int|NULL $translator_id
 * @property-read Nextras\Orm\EntityCollection $tags
 */
class Book extends Nextras\Orm\Entity
{

	protected function getAuthor()
	{
		return new Author($this->ref('author'));
	}



	protected function setAuthor(Author $author)
	{
		if ($author->isPersisted()) {
			$this->author_id = $author->id;
		} else {
			$author->onPersist[] = function(Author $author) {
				$this->author_id = $author->id;
			};
		}
	}



	protected function getTranslator()
	{
		return new Author($this->ref('author', 'translator_id'));
	}



	protected function setTranslator($translator)
	{
		if ($translator instanceof Author) {
			if ($translator->isPersisted()) {
				$this->translator_id = $translator->id;
			} else {
				$translator->onPersist[] = function($translator) {
					$this->translator_id = $translator->id;
				};
			}
		} else {
			$this->translator_id = NULL;
		}
	}



	protected function getTags()
	{
		return new EntityCollection($this->related('book_tag'), 'Tag', 'tag');
	}

}
