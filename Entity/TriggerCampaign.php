<?php
namespace MauticPlugin\MauticTriggerdialogBundle\Entity;

use Doctrine\ORM\Mapping\ClassMetadata;
use Mautic\CoreBundle\Doctrine\Mapping\ClassMetadataBuilder;
use Mautic\CoreBundle\Entity\FormEntity;
use MauticPlugin\MauticTriggerdialogBundle\Validator\AllowedCharacters;
use MauticPlugin\MauticTriggerdialogBundle\Validator\Variable;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Mapping\ClassMetadata as SymfonyClassMetadata;

class TriggerCampaign extends FormEntity
{
    const ALLOWED_TYPES = [
        'string' => 'plugin.triggerdialog.form.types.string',
        'integer' => 'plugin.triggerdialog.form.types.integer',
        'boolean' => 'plugin.triggerdialog.form.types.boolean',
        'date' => 'plugin.triggerdialog.form.types.date',
        'image' => 'plugin.triggerdialog.form.types.image',
        'imageurl' => 'plugin.triggerdialog.form.types.imageurl',
        'float' => 'plugin.triggerdialog.form.types.float',
        'zip' => 'plugin.triggerdialog.form.types.zip',
        'countrycode' => 'plugin.triggerdialog.form.types.countrycode',
    ];

    private $id;

    private $triggerId = 0;

    private $mailingId = 0;

    private $name = '';

    private $description = '';

    /**
     * @var \DateTimeInterface
     */
    private $startDate;

    /**
     * @var null|\DateTimeInterface
     */
    private $endDate;

    private $variables = [];

    private $printNodeId = '';

    private $printNodeDescription = '';

    /**
     * {@inheritdoc}
     */
    public function __clone()
    {
        $this->id = null;

        parent::__clone();
    }

    public function __construct()
    {
        $this->setStartDate();
    }

    /**
     * {@inheritdoc}
     *
     * @param ClassMetadata $metadata
     */
    public static function loadMetadata(ClassMetadata $metadata)
    {
        $builder = new ClassMetadataBuilder($metadata);
        $builder->setTable('trigger_campaigns')->setCustomRepositoryClass(TriggerCampaignRepository::class);
        $builder->addIdColumns();
        $builder->createField('mailingId', 'integer')->columnName('mailing_id')->build();
        $builder->createField('triggerId', 'integer')->columnName('trigger_id')->build();
        $builder->createField('startDate', 'datetime')->columnName('start_date')->build();
        $builder->createField('endDate', 'datetime')->columnName('end_date')->nullable()->build();
        $builder->createField('printNodeId', 'string')->columnName('print_node_id')->build();
        $builder->createField('printNodeDescription', 'string')->columnName('print_node_description')->build();
        $builder->createField('variables', 'array')->build();
    }

    /**
     * @param SymfonyClassMetadata $metadata
     */
    public static function loadValidatorMetadata(SymfonyClassMetadata $metadata)
    {
        $metadata->addPropertyConstraints('name', [
            new NotBlank([
                'message' => 'mautic.core.name.required',
            ]),
            new Length([
                'min' => 1,
                'max' => 30,
            ]),
            new AllowedCharacters(),
        ]);

        $metadata->addPropertyConstraints('printNodeId', [
            new Length([
                'min' => 1,
                'max' => 32,
            ]),
        ]);

        $metadata->addPropertyConstraints('printNodeDescription', [
            new Length([
                'min' => 1,
                'max' => 30,
            ]),
            new AllowedCharacters(),
        ]);

        $metadata->addPropertyConstraints('variables', [
            new Variable(),
        ]);
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->isChanged('name', $name);
        $this->name = $name;
    }

    public function getDescription(): string
    {
        return $this->description ?? '';
    }

    public function setDescription(?string $description): void
    {
        $description = (string)$description;
        $this->isChanged('description', $description);
        $this->description = $description;
    }

    public function getStartDate(): \DateTimeInterface
    {
        return $this->startDate;
    }

    public function setStartDate(?\DateTimeInterface $startDate = null): void
    {
        if ($startDate instanceof \DateTimeInterface === false) {
            $startDate = new \DateTime();
        }
        if ($startDate === null){
            $this->isChanged('startDate', $startDate);
        }
        $this->startDate = $startDate;
    }

    public function getEndDate(): ?\DateTimeInterface
    {
        return $this->endDate;
    }

    public function setEndDate(?\DateTimeInterface $endDate = null): void
    {
        $this->isChanged('endDate', $endDate);
        $this->endDate = $endDate;
    }

    public function getVariables(): array
    {
        return $this->variables;
    }

    public function setVariables(array $variables): void
    {
        $this->isChanged('variables', $variables);
        $this->variables = $variables;
    }

    public function getPrintNodeId(): string
    {
        return $this->printNodeId;
    }

    public function setPrintNodeId(string $printNodeId): void
    {
        $this->isChanged('printNodeId', $printNodeId);
        $this->printNodeId = $printNodeId;
    }

    public function getPrintNodeDescription(): string
    {
        return $this->printNodeDescription;
    }

    public function setPrintNodeDescription(string $printNodeDescription): void
    {
        $this->isChanged('printNodeDescription', $printNodeDescription);
        $this->printNodeDescription = $printNodeDescription;
    }

    public function getTriggerId(): int
    {
        return $this->triggerId;
    }

    public function setTriggerId(int $triggerId): void
    {
        $this->isChanged('triggerId', $triggerId);
        $this->triggerId = $triggerId;
    }

    public function getMailingId(): int
    {
        return $this->mailingId;
    }

    public function setMailingId(int $mailingId): void
    {
        $this->isChanged('mailingId', $mailingId);
        $this->mailingId = $mailingId;
    }
}
