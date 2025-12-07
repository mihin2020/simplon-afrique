<?php

namespace App\Livewire\Admin;

use App\Models\EvaluationCategory;
use App\Models\EvaluationCriterion;
use App\Models\EvaluationGrid;
use App\Models\LabellisationStep;
use Livewire\Component;

class EvaluationGridDetail extends Component
{
    public string $gridId;

    public ?EvaluationGrid $grid = null;

    // Category form fields
    public $showCategoryModal = false;

    public $categoryId = null;

    public $categoryName = '';

    public $categoryDescription = '';

    public $categoryStepId = null;

    // Criterion form fields
    public $showCriterionModal = false;

    public $criterionCategoryId = null;

    public $criterionId = null;

    public $criterionName = '';

    public $criterionDescription = '';

    public $criterionWeight = 0;

    protected $rules = [
        'categoryName' => ['required', 'string', 'max:255'],
        'categoryDescription' => ['nullable', 'string'],
        'categoryStepId' => ['required', 'exists:labellisation_steps,id'],
        'criterionName' => ['required', 'string', 'max:255'],
        'criterionDescription' => ['nullable', 'string'],
        'criterionWeight' => ['required', 'numeric', 'min:0', 'max:100'],
    ];

    protected $messages = [
        'categoryName.required' => 'Le nom de la catégorie est obligatoire.',
        'categoryStepId.required' => 'L\'étape de labellisation est obligatoire.',
        'categoryStepId.exists' => 'L\'étape de labellisation sélectionnée n\'existe pas.',
        'criterionName.required' => 'Le nom du critère est obligatoire.',
        'criterionWeight.required' => 'Le poids est obligatoire.',
        'criterionWeight.numeric' => 'Le poids doit être un nombre.',
        'criterionWeight.min' => 'Le poids ne peut pas être négatif.',
        'criterionWeight.max' => 'Le poids ne peut pas dépasser 100%.',
    ];

    public function mount(string $gridId): void
    {
        $this->gridId = $gridId;
        $this->loadGrid();
    }

    public function loadGrid(): void
    {
        $this->grid = EvaluationGrid::with([
            'categories' => function ($query) {
                $query->orderBy('display_order');
            },
            'categories.labellisationStep',
            'categories.criteria' => function ($query) {
                $query->orderBy('display_order');
            },
        ])->findOrFail($this->gridId);
    }

    // Category methods
    public function openCategoryModal(?string $categoryId = null): void
    {
        $this->categoryId = $categoryId;
        if ($categoryId) {
            $category = EvaluationCategory::findOrFail($categoryId);
            $this->categoryName = $category->name;
            $this->categoryDescription = $category->description;
            $this->categoryStepId = $category->labellisation_step_id;
        } else {
            $this->resetCategoryForm();
        }
        $this->showCategoryModal = true;
    }

    public function closeCategoryModal(): void
    {
        $this->showCategoryModal = false;
        $this->resetCategoryForm();
    }

    public function resetCategoryForm(): void
    {
        $this->categoryId = null;
        $this->categoryName = '';
        $this->categoryDescription = '';
        $this->categoryStepId = null;
    }

    public function saveCategory(): void
    {
        $this->validateOnly('categoryName');
        $this->validateOnly('categoryDescription');
        $this->validateOnly('categoryStepId');

        if ($this->categoryId) {
            $category = EvaluationCategory::findOrFail($this->categoryId);
            $category->update([
                'name' => $this->categoryName,
                'description' => $this->categoryDescription,
                'labellisation_step_id' => $this->categoryStepId,
            ]);
            session()->flash('success', 'La catégorie a été modifiée avec succès.');
        } else {
            $maxOrder = EvaluationCategory::where('evaluation_grid_id', $this->gridId)->max('display_order') ?? 0;
            EvaluationCategory::create([
                'evaluation_grid_id' => $this->gridId,
                'name' => $this->categoryName,
                'description' => $this->categoryDescription,
                'labellisation_step_id' => $this->categoryStepId,
                'display_order' => $maxOrder + 1,
            ]);
            session()->flash('success', 'La catégorie a été créée avec succès.');
        }

        $this->closeCategoryModal();
        $this->loadGrid();
    }

    public function deleteCategory(string $categoryId): void
    {
        $category = EvaluationCategory::findOrFail($categoryId);
        $categoryName = $category->name;
        $category->delete();

        // Réorganiser les display_order
        $this->reorderCategories();

        session()->flash('success', "La catégorie « {$categoryName} » a été supprimée avec succès.");
        $this->loadGrid();
    }

    public function moveCategoryUp(string $categoryId): void
    {
        $category = EvaluationCategory::findOrFail($categoryId);
        if ($category->display_order > 1) {
            $previousCategory = EvaluationCategory::where('evaluation_grid_id', $this->gridId)
                ->where('display_order', $category->display_order - 1)
                ->first();

            if ($previousCategory) {
                $tempOrder = $category->display_order;
                $category->update(['display_order' => $previousCategory->display_order]);
                $previousCategory->update(['display_order' => $tempOrder]);
            }
        }
        $this->loadGrid();
    }

    public function moveCategoryDown(string $categoryId): void
    {
        $category = EvaluationCategory::findOrFail($categoryId);
        $maxOrder = EvaluationCategory::where('evaluation_grid_id', $this->gridId)->max('display_order') ?? 0;

        if ($category->display_order < $maxOrder) {
            $nextCategory = EvaluationCategory::where('evaluation_grid_id', $this->gridId)
                ->where('display_order', $category->display_order + 1)
                ->first();

            if ($nextCategory) {
                $tempOrder = $category->display_order;
                $category->update(['display_order' => $nextCategory->display_order]);
                $nextCategory->update(['display_order' => $tempOrder]);
            }
        }
        $this->loadGrid();
    }

    private function reorderCategories(): void
    {
        $categories = EvaluationCategory::where('evaluation_grid_id', $this->gridId)
            ->orderBy('display_order')
            ->get();

        foreach ($categories as $index => $category) {
            $category->update(['display_order' => $index + 1]);
        }
    }

    // Criterion methods
    public function openCriterionModal(string $categoryId, ?string $criterionId = null): void
    {
        $this->criterionCategoryId = $categoryId;
        $this->criterionId = $criterionId;
        if ($criterionId) {
            $criterion = EvaluationCriterion::findOrFail($criterionId);
            $this->criterionName = $criterion->name;
            $this->criterionDescription = $criterion->description;
            $this->criterionWeight = $criterion->weight;
        } else {
            $this->resetCriterionForm();
        }
        $this->showCriterionModal = true;
    }

    public function closeCriterionModal(): void
    {
        $this->showCriterionModal = false;
        $this->resetCriterionForm();
    }

    public function resetCriterionForm(): void
    {
        $this->criterionId = null;
        $this->criterionName = '';
        $this->criterionDescription = '';
        $this->criterionWeight = 0;
    }

    public function saveCriterion(): void
    {
        $this->validateOnly('criterionName');
        $this->validateOnly('criterionDescription');
        $this->validateOnly('criterionWeight');

        // Validation supplémentaire : vérifier que le total des poids ne dépasse pas 100%
        if ($this->criterionId) {
            // Modification : calculer sans le critère actuel
            $currentCriterion = EvaluationCriterion::findOrFail($this->criterionId);
            $totalWeight = EvaluationCriterion::where('evaluation_category_id', $this->criterionCategoryId)
                ->where('id', '!=', $this->criterionId)
                ->sum('weight');
        } else {
            // Création : calculer avec tous les critères existants
            $totalWeight = EvaluationCriterion::where('evaluation_category_id', $this->criterionCategoryId)
                ->sum('weight');
            $currentCriterion = null;
        }

        if (($totalWeight + $this->criterionWeight) > 100) {
            $this->addError('criterionWeight', "Le total des poids ne peut pas dépasser 100%. Poids actuel : {$totalWeight}%");

            return;
        }

        if ($this->criterionId) {
            $criterion = EvaluationCriterion::findOrFail($this->criterionId);
            $criterion->update([
                'name' => $this->criterionName,
                'description' => $this->criterionDescription,
                'weight' => $this->criterionWeight,
            ]);
            session()->flash('success', 'Le critère a été modifié avec succès.');
        } else {
            $maxOrder = EvaluationCriterion::where('evaluation_category_id', $this->criterionCategoryId)->max('display_order') ?? 0;
            EvaluationCriterion::create([
                'evaluation_category_id' => $this->criterionCategoryId,
                'name' => $this->criterionName,
                'description' => $this->criterionDescription,
                'weight' => $this->criterionWeight,
                'display_order' => $maxOrder + 1,
            ]);
            session()->flash('success', 'Le critère a été créé avec succès.');
        }

        $this->closeCriterionModal();
        $this->loadGrid();
    }

    public function deleteCriterion(string $criterionId): void
    {
        $criterion = EvaluationCriterion::findOrFail($criterionId);
        $criterionName = $criterion->name;
        $criterion->delete();

        // Réorganiser les display_order
        $this->reorderCriteria($criterion->evaluation_category_id);

        session()->flash('success', "Le critère « {$criterionName} » a été supprimé avec succès.");
        $this->loadGrid();
    }

    public function moveCriterionUp(string $criterionId): void
    {
        $criterion = EvaluationCriterion::findOrFail($criterionId);
        if ($criterion->display_order > 1) {
            $previousCriterion = EvaluationCriterion::where('evaluation_category_id', $criterion->evaluation_category_id)
                ->where('display_order', $criterion->display_order - 1)
                ->first();

            if ($previousCriterion) {
                $tempOrder = $criterion->display_order;
                $criterion->update(['display_order' => $previousCriterion->display_order]);
                $previousCriterion->update(['display_order' => $tempOrder]);
            }
        }
        $this->loadGrid();
    }

    public function moveCriterionDown(string $criterionId): void
    {
        $criterion = EvaluationCriterion::findOrFail($criterionId);
        $maxOrder = EvaluationCriterion::where('evaluation_category_id', $criterion->evaluation_category_id)->max('display_order') ?? 0;

        if ($criterion->display_order < $maxOrder) {
            $nextCriterion = EvaluationCriterion::where('evaluation_category_id', $criterion->evaluation_category_id)
                ->where('display_order', $criterion->display_order + 1)
                ->first();

            if ($nextCriterion) {
                $tempOrder = $criterion->display_order;
                $criterion->update(['display_order' => $nextCriterion->display_order]);
                $nextCriterion->update(['display_order' => $tempOrder]);
            }
        }
        $this->loadGrid();
    }

    private function reorderCriteria(string $categoryId): void
    {
        $criteria = EvaluationCriterion::where('evaluation_category_id', $categoryId)
            ->orderBy('display_order')
            ->get();

        foreach ($criteria as $index => $criterion) {
            $criterion->update(['display_order' => $index + 1]);
        }
    }

    public function getCategoryTotalWeightProperty($categoryId): float
    {
        return EvaluationCriterion::where('evaluation_category_id', $categoryId)->sum('weight');
    }

    /**
     * Vérifie si une catégorie a une somme de poids égale à 100%.
     */
    public function isCategoryComplete(string $categoryId): bool
    {
        $totalWeight = EvaluationCriterion::where('evaluation_category_id', $categoryId)->sum('weight');

        return abs($totalWeight - 100) < 0.01; // Tolérance pour les erreurs d'arrondi
    }

    /**
     * Retourne le poids total d'une catégorie.
     */
    public function getCategoryWeight(string $categoryId): float
    {
        return EvaluationCriterion::where('evaluation_category_id', $categoryId)->sum('weight');
    }

    /**
     * Vérifie si la grille est valide (toutes les catégories ont 100% de poids).
     */
    public function isGridValid(): bool
    {
        if (! $this->grid) {
            return false;
        }

        foreach ($this->grid->categories as $category) {
            if (! $this->isCategoryComplete($category->id)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Retourne les catégories incomplètes (poids != 100%).
     */
    public function getIncompleteCategoriesProperty(): array
    {
        if (! $this->grid) {
            return [];
        }

        $incomplete = [];
        foreach ($this->grid->categories as $category) {
            $totalWeight = $this->getCategoryWeight($category->id);
            if (abs($totalWeight - 100) >= 0.01) {
                $incomplete[] = [
                    'id' => $category->id,
                    'name' => $category->name,
                    'total_weight' => $totalWeight,
                    'missing' => 100 - $totalWeight,
                ];
            }
        }

        return $incomplete;
    }

    public function render()
    {
        $labellisationSteps = LabellisationStep::orderBy('display_order')->get();

        if (! $this->grid) {
            return view('livewire.admin.evaluation-grid-detail', [
                'grid' => null,
                'incompleteCategories' => [],
                'isGridValid' => false,
                'labellisationSteps' => $labellisationSteps,
            ]);
        }

        return view('livewire.admin.evaluation-grid-detail', [
            'grid' => $this->grid,
            'incompleteCategories' => $this->incompleteCategories,
            'isGridValid' => $this->isGridValid(),
            'labellisationSteps' => $labellisationSteps,
        ]);
    }
}
