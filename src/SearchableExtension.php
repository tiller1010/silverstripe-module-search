<?php
/**/
namespace Werkbot\Search;
/**/
use SilverStripe\ORM\DB;
use SilverStripe\Core\ClassInfo;
use SilverStripe\ORM\DataExtension;
use SilverStripe\Versioned\Versioned;
/**/
class SearchableExtension extends DataExtension
{
  /*
    Column names for the "Title" and "Content" search fields
    Override these to set them to a different column name
  */
  public $SearchableExtension_Title_ColumnName = "Title";
  public $SearchableExtension_Summary_ColumnName = "Content";
  /**/
  private static $casting = [
    "getSearchableTitle" => "Text",
    "getSearchableSummary" => 'HTMLText',
  ];

  // TEST CHANGE
  // ANOTHER TEST CHANGE
  // 2.1.0 change

  //  SUPPORT CHANGE.
  // I WILL SQUASH THIS IN A FEATURE BRANCH

  //  SUPPORT CHANGE.
  // I WILL SQUASH THIS IN A FEATURE BRANCH

  // CHANGE
  // SOME CHANGE WITH A HOTFIX CHANGELOG NOTE
  // SOME CHANGE WITH A HOTFIX CHANGELOG NOTE

  // REPO UPDATE CHANGE
  // PAYLOAD REPO UPDATE CHANGE

  /**
   * getIndexQuery
   * This query is used when building the index
   *
   * @return string/boolean - FALSE if not set
   * Example:
      SELECT
        concat(\"Page_\", SiteTree.ID) AS ID,
        SiteTree.ClassName,
        SiteTree.Title,
        SiteTree.Content
      FROM
        Page
      LEFT JOIN
        SiteTree
      ON
        SiteTree.ID = Page.ID
      WHERE
        SiteTree.ShowInSearch = '1'";
  **/
  public function getIndexQuery()
  {
      return false;
  }
  /**
   * getSearchableTitle
   * Returns the title, to be used in search results
   * Override if Title uses a different variable name
   *
   * @return string
  **/
  public function getSearchableTitle()
  {
      if ($this->owner->SearchableExtension_Title_ColumnName) {
          return $this->owner->{$this->owner->SearchableExtension_Title_ColumnName};
      } else {
          return $this->owner->Title;
      }
  }
  /**
   * getSearchableTitleColumnName
   * Returns the name of the Title Column, "Title" is returned if the
   * SearchableExtension_Title_ColumnName is not overridden
   * @return string
  **/
  public function getSearchableTitleColumnName()
  {
      if ($this->owner->SearchableExtension_Title_ColumnName) {
          return $this->owner->SearchableExtension_Title_ColumnName;
      } else {
          return "Title";
      }
  }
  /**
   * getSearchableSummary
   * Returns the content to be used in search results
   * Override if Content uses a different variable name
   *
   * @return string
  **/
  public function getSearchableSummary()
  {
      if ($this->owner->SearchableExtension_Summary_ColumnName) {
          return $this->owner->{$this->owner->SearchableExtension_Summary_ColumnName};
      } else {
          return $this->owner->Content;
      }
  }
  /**
   * getSearchableContent
   * Returns the content to be used when indexing this record
   * Override if Content uses a different variable name or to include more searchable content:
   *
   * Example, the specified column data from all child records will be concatendated to the searchable content:
   * return $this->Content . implode(',', $this->Children()->column('SearchTerm'));
   *
   * @return string
  **/
  public function getSearchableContent()
  {
    return $this->getSearchableSummary();
  }
  /**
   * getSearchableSummaryColumnName
   * Returns the name of the Summary Column, "Content" is returned if the
   * SearchableExtension_Summary_ColumnName is not overridden
   * @return string
  **/
  public function getSearchableSummaryColumnName()
  {
      if ($this->owner->SearchableExtension_Summary_ColumnName) {
          return $this->owner->SearchableExtension_Summary_ColumnName;
      } else {
          return "Content";
      }
  }
  /**
   * insertIndex
   *
   * @return void
  **/
  public function insertIndex()
  {
      $index = TNTSearchHelper::Instance()->getTNTSearchIndex();
      $index->insert([
        'ID' => ClassInfo::shortName($this->owner->ClassName)."_".$this->owner->ID,
        'ClassName' => $this->owner->ClassName,
        'Title' => $this->owner->getSearchableTitle(),
        'Content' => $this->owner->getSearchableContent(),
      ]);
  }
  /**
   * updateIndex
   *
   * @return void
  **/
  public function updateIndex()
  {
      $index = TNTSearchHelper::Instance()->getTNTSearchIndex();
      $index->update(
          ClassInfo::shortName($this->owner->ClassName)."_".$this->owner->ID,
          [
            'ID' => ClassInfo::shortName($this->owner->ClassName)."_".$this->owner->ID,
            'ClassName' => $this->owner->ClassName,
            'Title' => $this->owner->getSearchableTitle(),
            'Content' => $this->owner->getSearchableContent(),
          ]
      );
  }
  /**
   * deleteIndex
   *
   * @return void
  **/
  public function deleteIndex()
  {
      $index = TNTSearchHelper::Instance()->getTNTSearchIndex();
      $index->delete(ClassInfo::shortName($this->owner->ClassName)."_".$this->owner->ID);
  }
  /**
   * onBeforeWrite
   *
   * @return void
  **/
  public function onBeforeWrite()
  {
      if ($this->owner->isInDB() && !$this->owner->hasExtension(Versioned::class)) {
          if ($this->owner->isChanged($this->owner->SearchableExtension_Title_ColumnName) || $this->owner->isChanged($this->owner->SearchableExtension_Summary_ColumnName)) {
              $this->owner->updateIndex();
          }
      }
      parent::onBeforeWrite();
  }
  /**
   * onAfterWrite
   *
   * @return void
  **/
  public function onAfterWrite()
  {
      if ($this->owner->isChanged('ID') && !$this->owner->hasExtension(Versioned::class)) {
          $this->owner->insertIndex();
      }
      parent::onAfterWrite();
  }
  /**
   * onBeforePublish
   *
   * @return void
  **/
  public function onBeforePublish()
  {
      if (!$this->owner->isPublished()) {
          $this->owner->insertIndex();
      }
  }
  /**
   * onAfterPublish
   *
   * @return void
  **/
  public function onAfterPublish()
  {
      $this->owner->updateIndex();
  }
  /**
   * onAfterUnpublish
   *
   * @return void
  **/
  public function onAfterUnpublish()
  {
      $this->owner->deleteIndex();
  }
  /**
   * onAfterDelete
   *
   * @return void
  **/
  public function onAfterDelete()
  {
      $this->owner->deleteIndex();
  }
  /**
   * requireDefaultRecords
   * Runs the index on a dev/build
  **/
  public function requireDefaultRecords()
  {
    // Test comment
    // Test comment
      parent::requireDefaultRecords();

      if (!file_exists(dirname(__DIR__, 4).'/search')) {
          mkdir(dirname(__DIR__, 4).'/search');
          echo "Created search folder<br /><br />";
      }
      $indexer = TNTSearchHelper::Instance()->getTNTSearchIndex(true);
      if ($query = $this->owner->getIndexQuery()) {
          $indexer->query($query);
          $indexer->run();
          DB::alteration_message('Indexing...'.$this->owner->ClassName, 'created');
      }
  }
}
