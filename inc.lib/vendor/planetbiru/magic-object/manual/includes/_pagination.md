## Filtering, Ordering and Pagination

MagicObject will filter data according to the given criteria. On the other hand, MagicObject will only retrieve data on the specified page by specifying `limit` and `offset` data in the `select` query.

Example parameters:

`genre_id=0648d4e176da4df4472d&album_id=&artist_vocal_id=&name=&vocal=&lyric_complete=&active=&page=2&orderby=title&ordertype=asc`

Create entity according to database

```php
<?php

namespace MusicProductionManager\Data\Entity;

use MagicObject\MagicObject;

/**
 * @Entity
 * @JSON(property-naming-strategy=SNAKE_CASE)
 * @Table(name="song")
 */
class EntitySong extends MagicObject
{
	/**
	 * Song ID
	 * 
	 * @Id
	 * @GeneratedValue(strategy=GenerationType.UUID)
	 * @NotNull
	 * @Column(name="song_id", type="varchar(50)", length=50, nullable=false)
	 * @Label(content="Song ID")
	 * @var string
	 */
	protected $songId;

	/**
	 * Random Song ID
	 * 
	 * @Column(name="random_song_id", type="varchar(50)", length=50, nullable=true)
	 * @Label(content="Random Song ID")
	 * @var string
	 */
	protected $randomSongId;

	/**
	 * Name
	 * 
	 * @Column(name="name", type="varchar(100)", length=100, nullable=true)
	 * @Label(content="Name")
	 * @var string
	 */
	protected $name;

	/**
	 * Title
	 * 
	 * @Column(name="title", type="text", nullable=true)
	 * @Label(content="Title")
	 * @var string
	 */
	protected $title;

	/**
	 * Album ID
	 * 
	 * @Column(name="album_id", type="varchar(50)", length=50, nullable=true)
	 * @Label(content="Album ID")
	 * @var string
	 */
	protected $albumId;

	/**
	 * Album
	 * @JoinColumn(name="album_id")
	 * @Label(content="Album")
	 * @var Album
	 */
	protected $album;

	/**
	 * Track Number
	 * 
	 * @Column(name="track_number", type="int(11)", length=11, nullable=true)
	 * @Label(content="Track Number")
	 * @var int
	 */
	protected $trackNumber;

	/**
	 * Producer ID
	 * 
	 * @Column(name="producer_id", type="varchar(40)", length=40, nullable=true)
	 * @Label(content="Producer ID")
	 * @var string
	 */
	protected $producerId;

	/**
	 * Producer
	 * 
	 * @JoinColumn(name="producer_id")
	 * @Label(content="Producer")
	 * @var Producer
	 */
	protected $producer;

	/**
	 * Artist Vocal
	 * 
	 * @Column(name="artist_vocalist", type="varchar(50)", length=50, nullable=true)
	 * @Label(content="Artist Vocal")
	 * @var string
	 */
	protected $artistVocalist;

	/**
	 * Artist Vocal
	 * 
	 * @JoinColumn(name="artist_vocalist")
	 * @Label(content="Artist Vocal")
	 * @var Artist
	 */
	protected $vocalist;

	/**
	 * Artist Composer
	 * 
	 * @Column(name="artist_composer", type="varchar(50)", length=50, nullable=true)
	 * @Label(content="Artist Composer")
	 * @var string
	 */
	protected $artistComposer;

	/**
	 * Artist Composer
	 * 
	 * @JoinColumn(name="artist_composer")
	 * @Label(content="Artist Composer")
	 * @var Artist
	 */
	protected $composer;

	/**
	 * Artist Arranger
	 * 
	 * @Column(name="artist_arranger", type="varchar(50)", length=50, nullable=true)
	 * @Label(content="Artist Arranger")
	 * @var string
	 */
	protected $artistArranger;

	/**
	 * Artist Arranger
	 * 
	 * @JoinColumn(name="artist_arranger")
	 * @Label(content="Artist Arranger")
	 * @var Artist
	 */
	protected $arranger;

	/**
	 * File Path
	 * 
	 * @Column(name="file_path", type="text", nullable=true)
	 * @Label(content="File Path")
	 * @var string
	 */
	protected $filePath;

	/**
	 * File Name
	 * 
	 * @Column(name="file_name", type="varchar(100)", length=100, nullable=true)
	 * @Label(content="File Name")
	 * @var string
	 */
	protected $fileName;

	/**
	 * File Type
	 * 
	 * @Column(name="file_type", type="varchar(100)", length=100, nullable=true)
	 * @Label(content="File Type")
	 * @var string
	 */
	protected $fileType;

	/**
	 * File Extension
	 * 
	 * @Column(name="file_extension", type="varchar(20)", length=20, nullable=true)
	 * @Label(content="File Extension")
	 * @var string
	 */
	protected $fileExtension;

	/**
	 * File Size
	 * 
	 * @Column(name="file_size", type="bigint(20)", length=20, nullable=true)
	 * @Label(content="File Size")
	 * @var int
	 */
	protected $fileSize;

	/**
	 * File Md5
	 * 
	 * @Column(name="file_md5", type="varchar(32)", length=32, nullable=true)
	 * @Label(content="File Md5")
	 * @var string
	 */
	protected $fileMd5;

	/**
	 * File Upload Time
	 * 
	 * @Column(name="file_upload_time", type="timestamp", length=26, nullable=true)
	 * @Label(content="File Upload Time")
	 * @var string
	 */
	protected $fileUploadTime;

	/**
	 * First Upload Time
	 * 
	 * @Column(name="first_upload_time", type="timestamp", length=26, nullable=true)
	 * @Label(content="First Upload Time")
	 * @var string
	 */
	protected $firstUploadTime;

	/**
	 * Last Upload Time
	 * 
	 * @Column(name="last_upload_time", type="timestamp", length=26, nullable=true)
	 * @Label(content="Last Upload Time")
	 * @var string
	 */
	protected $lastUploadTime;

	/**
	 * File Path Midi
	 * 
	 * @Column(name="file_path_midi", type="text", nullable=true)
	 * @Label(content="File Path Midi")
	 * @var string
	 */
	protected $filePathMidi;

	/**
	 * Last Upload Time Midi
	 * 
	 * @Column(name="last_upload_time_midi", type="timestamp", length=26, nullable=true)
	 * @Label(content="Last Upload Time Midi")
	 * @var string
	 */
	protected $lastUploadTimeMidi;

	/**
	 * File Path Xml
	 * 
	 * @Column(name="file_path_xml", type="text", nullable=true)
	 * @Label(content="File Path Xml")
	 * @var string
	 */
	protected $filePathXml;

	/**
	 * Last Upload Time Xml
	 * 
	 * @Column(name="last_upload_time_xml", type="timestamp", length=26, nullable=true)
	 * @Label(content="Last Upload Time Xml")
	 * @var string
	 */
	protected $lastUploadTimeXml;

	/**
	 * File Path Pdf
	 * 
	 * @Column(name="file_path_pdf", type="text", nullable=true)
	 * @Label(content="File Path Pdf")
	 * @var string
	 */
	protected $filePathPdf;

	/**
	 * Last Upload Time Pdf
	 * 
	 * @Column(name="last_upload_time_pdf", type="timestamp", length=26, nullable=true)
	 * @Label(content="Last Upload Time Pdf")
	 * @var string
	 */
	protected $lastUploadTimePdf;

	/**
	 * Duration
	 * 
	 * @Column(name="duration", type="float", nullable=true)
	 * @Label(content="Duration")
	 * @var float
	 */
	protected $duration;

	/**
	 * Genre ID
	 * 
	 * @Column(name="genre_id", type="varchar(50)", length=50, nullable=true)
	 * @Label(content="Genre ID")
	 * @var string
	 */
	protected $genreId;

	/**
	 * Genre ID
	 * 
	 * @JoinColumn(name="genre_id")
	 * @Label(content="Genre ID")
	 * @var Genre
	 */
	protected $genre;

	/**
	 * Bpm
	 * 
	 * @Column(name="bpm", type="float", nullable=true)
	 * @Label(content="Bpm")
	 * @var float
	 */
	protected $bpm;

	/**
	 * Time Signature
	 * 
	 * @Column(name="time_signature", type="varchar(40)", length=40, nullable=true)
	 * @Label(content="Time Signature")
	 * @var string
	 */
	protected $timeSignature;

	/**
	 * Subtitle
	 * 
	 * @Column(name="subtitle", type="longtext", nullable=true)
	 * @Label(content="Subtitle")
	 * @var string
	 */
	protected $subtitle;

	/**
	 * Subtitle Complete
	 * 
	 * @Column(name="subtitle_complete", type="tinyint(1)", length=1, nullable=true)
	 * @var bool
	 */
	protected $subtitleComplete;

	/**
	 * Lyric Midi
	 * 
	 * @Column(name="lyric_midi", type="longtext", nullable=true)
	 * @Label(content="Lyric Midi")
	 * @var string
	 */
	protected $lyricMidi;

	/**
	 * Lyric Midi Raw
	 * 
	 * @Column(name="lyric_midi_raw", type="longtext", nullable=true)
	 * @Label(content="Lyric Midi Raw")
	 * @var string
	 */
	protected $lyricMidiRaw;

	/**
	 * Vocal Guide
	 * 
	 * @Column(name="vocal_guide", type="longtext", nullable=true)
	 * @Label(content="Vocal Guide")
	 * @var string
	 */
	protected $vocalGuide;

	/**
	 * Vocal
	 * 
	 * @Column(name="vocal", type="tinyint(1)", length=1, nullable=true)
	 * @var bool
	 */
	protected $vocal;

	/**
	 * Instrument
	 * 
	 * @Column(name="instrument", type="longtext", nullable=true)
	 * @Label(content="Instrument")
	 * @var string
	 */
	protected $instrument;

	/**
	 * Midi Vocal Channel
	 * 
	 * @Column(name="midi_vocal_channel", type="int(11)", length=11, nullable=true)
	 * @Label(content="Midi Vocal Channel")
	 * @var int
	 */
	protected $midiVocalChannel;

	/**
	 * Rating
	 * 
	 * @Column(name="rating", type="float", nullable=true)
	 * @Label(content="Rating")
	 * @var float
	 */
	protected $rating;

	/**
	 * Comment
	 * 
	 * @Column(name="comment", type="longtext", nullable=true)
	 * @Label(content="Comment")
	 * @var string
	 */
	protected $comment;

	/**
	 * Image Path
	 * 
	 * @Column(name="image_path", type="text", nullable=true)
	 * @Label(content="Image Path")
	 * @var string
	 */
	protected $imagePath;

	/**
	 * Last Upload Time Image
	 * 
	 * @Column(name="last_upload_time_image", type="timestamp", length=26, nullable=true)
	 * @Label(content="Last Upload Time Image")
	 * @var string
	 */
	protected $lastUploadTimeImage;

	/**
	 * Time Create
	 * 
	 * @Column(name="time_create", type="timestamp", length=26, nullable=true, updatable=false)
	 * @Label(content="Time Create")
	 * @var string
	 */
	protected $timeCreate;

	/**
	 * Time Edit
	 * 
	 * @Column(name="time_edit", type="timestamp", length=26, nullable=true)
	 * @Label(content="Time Edit")
	 * @var string
	 */
	protected $timeEdit;

	/**
	 * IP Create
	 * 
	 * @Column(name="ip_create", type="varchar(50)", length=50, nullable=true, updatable=false)
	 * @Label(content="IP Create")
	 * @var string
	 */
	protected $ipCreate;

	/**
	 * IP Edit
	 * 
	 * @Column(name="ip_edit", type="varchar(50)", length=50, nullable=true)
	 * @Label(content="IP Edit")
	 * @var string
	 */
	protected $ipEdit;

	/**
	 * Admin Create
	 * 
	 * @Column(name="admin_create", type="varchar(50)", length=50, nullable=true, updatable=false)
	 * @Label(content="Admin Create")
	 * @var string
	 */
	protected $adminCreate;

	/**
	 * Admin Edit
	 * 
	 * @Column(name="admin_edit", type="varchar(50)", length=50, nullable=true)
	 * @Label(content="Admin Edit")
	 * @var string
	 */
	protected $adminEdit;

	/**
	 * Active
	 * 
	 * @Column(name="active", type="tinyint(1)", length=1, defaultValue="1", nullable=true)
	 * @DefaultColumn(value="1")
	 * @var bool
	 */
	protected $active;
}
```

Filtering and pagination

```php
<?php
use MagicObject\Database\PicoPageable;
use MagicObject\Database\PicoPage;
use MagicObject\Database\PicoSort;
use MagicObject\Database\PicoSortable;
use MagicObject\Pagination\PicoPagination;
use MagicObject\Request\PicoFilterConstant;
use MagicObject\Request\InputGet;
use MagicObject\Response\Generated\PicoSelectOption;
use MagicObject\Util\Dms;
use MusicProductionManager\Constants\ParamConstant;
use MusicProductionManager\Data\Entity\Album;
use MusicProductionManager\Data\Entity\Artist;
use MusicProductionManager\Data\Entity\EntitySong;
use MusicProductionManager\Data\Entity\EntitySongComment;
use MusicProductionManager\Data\Entity\Genre;
use MusicProductionManager\Data\Entity\Producer;
use MusicProductionManager\Utility\SpecificationUtil;
use MusicProductionManager\Utility\UserUtil;

require_once "inc/auth-with-login-form.php";
require_once "inc/header.php";

$inputGet = new InputGet();

$allowChangeVocalist = UserUtil::isAllowSelectVocalist($currentLoggedInUser);
$allowChangeComposer = UserUtil::isAllowSelectComposer($currentLoggedInUser);
$allowChangeArranger = UserUtil::isAllowSelectArranger($currentLoggedInUser);

?>
<div class="filter-container">
<form action="" method="get">
<div class="filter-group">
	<span>Genre</span>
	<select class="form-control" name="genre_id" id="genre_id">
		<option value="">- All -</option>
		<?php echo new PicoSelectOption(new Genre(null, $database), array('value'=>'genreId', 'label'=>'name'), $inputGet->getGenreId()); ?>
	</select>
</div>
<div class="filter-group">
	<span>Album</span>
	<select class="form-control" name="album_id" id="album_id">
		<option value="">- All -</option>
		<?php echo new PicoSelectOption(new Album(null, $database), array('value'=>'albumId', 'label'=>'name'), $inputGet->getAlbumId(), null, new PicoSortable('sortOrder', PicoSort::ORDER_TYPE_DESC)); ?>
	</select>
</div>
<div class="filter-group">
	<span>Producer</span>
	<select class="form-control" name="producer_id" id="producer_id">
		<option value="">- All -</option>
		<?php echo new PicoSelectOption(new Producer(null, $database), array('value'=>'producerId', 'label'=>'name'), $inputGet->getProducerId()); ?>
	</select>
</div>
<div class="filter-group">
	<span>Composer</span>
	<select class="form-control" name="composer" id="composer">
		<option value="">- All -</option>
		<?php echo new PicoSelectOption(new Artist(null, $database), array('value'=>'artistId', 'label'=>'name'), $inputGet->getComposer()); ?>
	</select>
</div>
<div class="filter-group">
	<span>Arranger</span>
	<select class="form-control" name="arranger" id="arranger">
		<option value="">- All -</option>
		<?php echo new PicoSelectOption(new Artist(null, $database), array('value'=>'artistId', 'label'=>'name'), $inputGet->getArranger()); ?>
	</select>
</div>
<div class="filter-group">
	<span>Vocalist</span>
	<select class="form-control" name="vocalist" id="vocalist">
		<option value="">- All -</option>
		<?php echo new PicoSelectOption(new Artist(null, $database), array('value'=>'artistId', 'label'=>'name'), $inputGet->getVocalist()); ?>
	</select>
</div>
<div class="filter-group">
	<span>Title</span>
	<input class="form-control" type="text" name="title" id="title" autocomplete="off" value="<?php echo $inputGet->getTitle(PicoFilterConstant::FILTER_SANITIZE_SPECIAL_CHARS);?>">
</div>
<div class="filter-group">
	<span>Subtitle</span>
	<input class="form-control" type="text" name="subtitle" id="subtitle" autocomplete="off" value="<?php echo $inputGet->getSubtitle(PicoFilterConstant::FILTER_SANITIZE_SPECIAL_CHARS);?>">
</div>

<div class="filter-group">
	<span>Subtitle Complete</span>
	<select class="form-control" name="subtitle_complete" id="subtitle_complete">
		<option value="">- All -</option>
		<option value="1"<?php echo $inputGet->createSelectedSubtitleComplete("1");?>>Yes</option>
		<option value="0"<?php echo $inputGet->createSelectedSubtitleComplete("0");?>>No</option>
	</select>
</div>

<div class="filter-group">
	<span>Vocal</span>
	<select class="form-control" name="vocal" id="vocal">
		<option value="">- All -</option>
		<option value="1"<?php echo $inputGet->createSelectedVocal("1");?>>Yes</option>
		<option value="0"<?php echo $inputGet->createSelectedVocal("0");?>>No</option>
	</select>
</div>

<div class="filter-group">
	<span>Active</span>
	<select class="form-control" name="active" id="active">
		<option value="">- All -</option>
		<option value="1"<?php echo $inputGet->createSelectedActive("1");?>>Yes</option>
		<option value="0"<?php echo $inputGet->createSelectedActive("0");?>>No</option>
	</select>
</div>

<input class="btn btn-success" type="submit" value="Show">

</form>
</div>
<?php
$orderMap = array(
'name'=>'name', 
'title'=>'title', 
'rating'=>'rating',
'albumId'=>'albumId', 
'album'=>'albumId', 
'trackNumber'=>'trackNumber',
'genreId'=>'genreId', 
'genre'=>'genreId',
'producerId'=>'producerId',
'artistVocalId'=>'artistVocalId',
'artistVocalist'=>'artistVocalId',
'artistComposer'=>'artistComposer',
'artistArranger'=>'artistArranger',
'duration'=>'duration',
'subtitleComplete'=>'subtitleComplete',
'vocal'=>'vocal',
'active'=>'active'
);
$defaultOrderBy = 'albumId';
$defaultOrderType = 'desc';
$pagination = new PicoPagination($cfg->getResultPerPage());

$spesification = SpecificationUtil::createSongSpecification($inputGet);

if($pagination->getOrderBy() == '')
{
$sortable = new PicoSortable();
$sort1 = new PicoSort('albumId', PicoSort::ORDER_TYPE_DESC);
$sortable->addSortable($sort1);
$sort2 = new PicoSort('trackNumber', PicoSort::ORDER_TYPE_ASC);
$sortable->addSortable($sort2);
}
else
{
$sortable = new PicoSortable($pagination->getOrderBy($orderMap, $defaultOrderBy), $pagination->getOrderType($defaultOrderType));
}

$pageable = new PicoPageable(new PicoPage($pagination->getCurrentPage(), $pagination->getPageSize()), $sortable);

$songEntity = new EntitySong(null, $database);
$rowData = $songEntity->findAll($spesification, $pageable, $sortable, true);

$result = $rowData->getResult();

?>

<script>
$(document).ready(function(e){
	let pg = new Pagination('.pagination', '.page-selector', 'data-page-number', 'page');
	pg.init();
	$(document).on('change', '.filter-container form select', function(e2){
		$(this).closest('form').submit();
	});
});
</script>

<?php
if(!empty($result))
{
?>
<div class="pagination">
<div class="pagination-number">
<?php
foreach($rowData->getPagination() as $pg)
{
	?><span class="page-selector<?php echo $pg['selected'] ? ' page-selected':'';?>" data-page-number="<?php echo $pg['page'];?>"><a href="#"><?php echo $pg['page'];?></a></span><?php
}
?>
</div>
</div>
<div class="table-list-container" style="overflow-x:auto">
<table class="table text-nowrap">
<thead>
	<tr>
	<th scope="col" width="20"><i class="ti ti-edit"></i></th>
	<th scope="col" width="20"><i class="ti ti-player-play"></i></th>
	<th scope="col" width="20"><i class="ti ti-download"></i></th>
	<th scope="col" width="20">#</th>
	<th scope="col" class="col-sort" data-name="name">Name</th>
	<th scope="col" class="col-sort" data-name="title">Title</th>
	<th scope="col" class="col-sort" data-name="rating">Rate</th>
	<th scope="col" class="col-sort" data-name="album_id">Album</th>
	<th scope="col" class="col-sort" data-name="producer_id">Producer</th>
	<th scope="col" class="col-sort" data-name="track_number">Trk</th>
	<th scope="col" class="col-sort" data-name="genre_id">Genre</th>
	<th scope="col" class="col-sort" data-name="artist_vocalist">Vocalist</th>
	<th scope="col" class="col-sort" data-name="artist_composer">Composer</th>
	<th scope="col" class="col-sort" data-name="artist_arranger">Arranger</th>
	<th scope="col" class="col-sort" data-name="duration">Duration</th>
	<th scope="col" class="col-sort" data-name="vocal">Vocal</th>
	<th scope="col" class="col-sort" data-name="subtitle_complete">Sub</th>
	<th scope="col" class="col-sort" data-name="active">Active</th>
	</tr>
</thead>
<tbody>
	<?php
	$no = $pagination->getOffset();
	foreach($result as $song)
	{
	$no++;
	$songId = $song->getSongId();
	$linkEdit = basename($_SERVER['PHP_SELF'])."?action=edit&song_id=".$songId;
	$linkDetail = basename($_SERVER['PHP_SELF'])."?action=detail&song_id=".$songId;
	$linkDelete = basename($_SERVER['PHP_SELF'])."?action=delete&song_id=".$songId;
	$linkDownload = "read-file.php?type=all&song_id=".$songId;
	?>
	<tr data-id="<?php echo $songId;?>">
	<th scope="row"><a href="<?php echo $linkEdit;?>" class="edit-data"><i class="ti ti-edit"></i></a></th>
	<th scope="row"><a href="#" class="play-data" data-url="<?php echo $cfg->getSongBaseUrl()."/".$song->getSongId()."/".basename($song->getFilePath());?>?hash=<?php echo str_replace(array(' ', '-', ':'), '', $song->getLastUploadTime());?>"><i class="ti ti-player-play"></i></a></th>
	<th scope="row"><a href="<?php echo $linkDownload;?>"><i class="ti ti-download"></i></a></th>
	<th class="text-right" scope="row"><?php echo $no;?></th>
	<td class="text-nowrap"><a href="<?php echo $linkDetail;?>" class="text-data text-data-name"><?php echo $song->getName();?></a></td>
	<td class="text-nowrap"><a href="<?php echo $linkDetail;?>" class="text-data text-data-title"><?php echo $song->getTitle();?></a></td>
	<td class="text-data text-data-rating text-nowrap"><?php echo $song->hasValueRating() ? $song->getRating() : "";?></td>
	<td class="text-data text-data-album-name text-nowrap"><?php echo $song->hasValueAlbum() ? $song->getAlbum()->getName() : "";?></td>
	<td class="text-data text-data-producer-name text-nowrap"><?php echo $song->hasValueProducer() ? $song->getProducer()->getName() : "";?></td>
	<td class="text-data text-data-track-number text-nowrap"><?php echo $song->hasValueTrackNumber() ? $song->getTrackNumber() : "";?></td>
	<td class="text-data text-data-genre-name text-nowrap"><?php echo $song->hasValueGenre() ? $song->getGenre()->getName() : "";?></td>
	<td class="text-data text-data-artist-vocal-name text-nowrap"><?php echo $song->hasValueVocalist() ? $song->getVocalist()->getName() : "";?></td>
	<td class="text-data text-data-artist-composer-name text-nowrap"><?php echo $song->hasValueComposer() ? $song->getComposer()->getName() : "";?></td>
	<td class="text-data text-data-artist-arranger-name text-nowrap"><?php echo $song->hasValueArranger() ? $song->getArranger()->getName() : "";?></td>
	<td class="text-data text-data-duration text-nowrap"><?php echo (new Dms())->ddToDms($song->getDuration() / 3600)->printDms(true, true); ?></td>
	<td class="text-data text-data-vocal text-nowrap"><?php echo $song->isVocal() ? 'Yes' : 'No';?></td>
	<td class="text-data text-data-subtitle-complete text-nowrap"><?php echo $song->isSsubtitleComplete() ? 'Yes' : 'No';?></td>
	<td class="text-data text-data-active text-nowrap"><?php echo $song->isActive() ? 'Yes' : 'No';?></td>
	</tr>
	<?php
	}
	?>
	
</tbody>
</table>
</div>

<div class="pagination">
<div class="pagination-number">
<?php
foreach($rowData->getPagination() as $pg)
{
	?><span class="page-selector<?php echo $pg['selected'] ? ' page-selected':'';?>" data-page-number="<?php echo $pg['page'];?>"><a href="#"><?php echo $pg['page'];?></a></span><?php
}
?>
</div>
</div>

<?php
}
?>

<script>
let playerModal;


$(document).ready(function(e){
let playerModalSelector = document.querySelector('#songPlayer');
playerModal = new bootstrap.Modal(playerModalSelector, {
	keyboard: false
});

$('a.play-data').on('click', function(e2){
	e2.preventDefault();
	$('#songPlayer').find('audio').attr('src', $(this).attr('data-url'));
	playerModal.show();
});
$('.close-player').on('click', function(e2){
	e2.preventDefault();
	$('#songPlayer').find('audio')[0].pause();
	playerModal.hide();
});
});
</script>

<div style="background-color: rgba(0, 0, 0, 0.11);" class="modal fade" id="songPlayer" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="songPlayerLabel" aria-hidden="true">
<div class="modal-dialog modal-dialog-centered">
	<div class="modal-content">
		<div class="modal-header">
			<h5 class="modal-title" id="addAlbumDialogLabel">Play Song</h5>
			<button type="button" class="btn-primary btn-close close-player" aria-label="Close"></button>
		</div>
		<div class="modal-body">
			<audio style="width: 100%; height: 40px;" controls></audio>
		</div>
		
		<div class="modal-footer">
			<button type="button" class="btn btn-success close-player">Close</button>
		</div>
	</div>
</div>
</div>

<div class="lazy-dom modal-container modal-update-data" data-url="lib.ajax/song-update-dialog.php"></div>

<script>
let updateSongModal;

$(document).ready(function(e){

$(document).on('click', '.edit-data', function(e2){
	e2.preventDefault();
	e2.stopPropagation();
	
	let songId = $(this).closest('tr').attr('data-id') || '';
	let dialogSelector = $('.modal-update-data');
	dialogSelector.load(dialogSelector.attr('data-url')+'?song_id='+songId, function(data){
	
	let updateSongModalElem = document.querySelector('#updateSongDialog');
	updateSongModal = new bootstrap.Modal(updateSongModalElem, {
		keyboard: false
	});
	updateSongModal.show();
	downloadForm('.lazy-dom-container', function(){
		if(!allDownloaded)
		{
			initModal2();
			console.log('loaded')
			allDownloaded = true;
		}
		loadForm();
	});
	})
});

$(document).on('click', '.save-update-song', function(){
	if($('.song-dialog audio').length > 0)
	{
	$('.song-dialog audio').each(function(){
		$(this)[0].pause();
	});
	}
	let dataSet = $(this).closest('form').serializeArray();
	$.ajax({
	type:'POST',
	url:'lib.ajax/song-update.php',
	data:dataSet, 
	dataType:'json',
	success: function(data)
	{
		updateSongModal.hide();
		let formData = getFormData(dataSet);
		let dataId = data.song_id;
		$('[data-id="'+dataId+'"] .text-data.text-data-name').text(data.name);
		$('[data-id="'+dataId+'"] .text-data.text-data-title').text(data.title);
		$('[data-id="'+dataId+'"] .text-data.text-data-rating').text(data.rating);
		$('[data-id="'+dataId+'"] .text-data.text-data-track-number').text(data.track_number);
		$('[data-id="'+dataId+'"] .text-data.text-data-artist-vocal-name').text(data.artist_vocal_name);
		$('[data-id="'+dataId+'"] .text-data.text-data-artist-composer-name').text(data.artist_composer_name);
		$('[data-id="'+dataId+'"] .text-data.text-data-artist-arranger-name').text(data.artist_arranger_name);
		$('[data-id="'+dataId+'"] .text-data.text-data-album-name').text(data.album_name);
		$('[data-id="'+dataId+'"] .text-data.text-data-genre-name').text(data.genre_name);
		$('[data-id="'+dataId+'"] .text-data.text-data-duration').text(data.duration);
		$('[data-id="'+dataId+'"] .text-data.text-data-vocal').text(data.vocal === true || data.vocal == 1 || data.vocal == "1" ?'Yes':'No');
		$('[data-id="'+dataId+'"] .text-data.text-data-active').text(data.active === true || data.active == 1 || data.active == "1" ?'Yes':'No');
	}
	})
});
});
</script>
<?php
require_once "inc/footer.php";
?>
```
