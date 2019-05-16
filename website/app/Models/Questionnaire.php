<?php

namespace App\Models;

use Dimsav\Translatable\Translatable;

/**
 * Class Questionnaire
 * @package App\Models
 *
 * @OA\Schema(
 *     description="Questionnaire model",
 *     title="Questionnaire model",
 *     required={"published", "level", "title"},
 *     @OA\Xml(
 *         name="Questionnaire"
 *     ),
 * )
 */
class Questionnaire extends AbstractModel
{
    /**
     * @OA\Property(),
     * @var integer
     */
    private $id;

    /**
     * @OA\Property()
     * @var boolean
     */
    private $published;

    /**
     * @OA\Property(
     *     enum={1, 2, 3},
     * ),
     * @var integer
     */
    private $level;

    /**
     * @OA\Property(),
     * @var integer
     */
    private $note_max;

    /**
     * @OA\Property(
     *     description="Questionnaire Themes",
     *     title="Questionnaire Themes",
     *     additionalItems=true,
     *     @OA\Xml(
     *         name="themes",
     *         wrapped=true
     *     ),
     * )
     *
     * @var Theme[]
     */
    private $themes;

    /**
     * @OA\Property(
     *     description="Questionnaire Sessions",
     *     title="Questionnaire Sessions",
     *     additionalItems=true,
     *     @OA\Xml(
     *         name="sessions",
     *         wrapped=true
     *     ),
     * )
     *
     * @var Session[]
     */
    private $sessions;

    /**
     * @OA\Property(
     *     description="Questionnaire Questions",
     *     title="Questionnaire Questions",
     *     additionalItems=true,
     *     @OA\Xml(
     *         name="questions",
     *         wrapped=true
     *     ),
     * )
     *
     * @var Question[]
     */
    private $questions;

    /**
     * @OA\Property(
     *     example="2017-02-02 18:31:45",
     *     format="datetime",
     *     type="string"
     * )
     *
     * @var \DateTime
     */
    private $created_at;

    /**
     * @OA\Property(
     *     example="2017-02-02 18:31:45",
     *     format="datetime",
     *     type="string"
     * )
     *
     * @var \DateTime
     */
    private $updated_at;

    /**
     * @OA\Property(
     *     default=true
     * );
     * @var boolean
     */
    private $active;

    /**
     * @OA\Property(),
     * @var string
     */
    private $title;

    /**
     * @OA\Property(),
     * @var string
     */
    private $description;

    /**
     * @OA\Property(),
     * @var integer
     */
    private $position;

    use Translatable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'published',
        'level',
        'note_max',
        'position',
        'session_id'
    ];

    /**
     * The attributes that are translated.
     *
     * @var array
     */
    public $translatedAttributes = [
        'active',
        'title',
        'description'
    ];

    public function sessions()
    {
        return $this->belongsToMany(Session::class);
    }

    public function themes()
    {
        return $this->belongsToMany(Theme::class);
    }

    public function questions()
    {
        return $this->belongsToMany(Question::class)->orderBy('position', 'asc')->withPivot('position');
    }
}
