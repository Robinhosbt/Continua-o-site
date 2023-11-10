<?php

include_once dirname(__FILE__) . '/components/startup.php';
include_once dirname(__FILE__) . '/components/application.php';
include_once dirname(__FILE__) . '/' . 'authorization.php';


include_once dirname(__FILE__) . '/' . 'database_engine/mysql_engine.php';
include_once dirname(__FILE__) . '/' . 'components/page/page_includes.php';

function GetConnectionOptions()
{
    $result = GetGlobalConnectionOptions();
    $result['client_encoding'] = 'utf8';
    GetApplication()->GetUserAuthentication()->applyIdentityToConnectionOptions($result);
    return $result;
}

class alunosPage extends Page
{
    protected function DoBeforeCreate()
    {
        $this->SetTitle('Alunos');
        $this->SetMenuLabel('Alunos');

        $this->dataset = new TableDataset(
            MySqlIConnectionFactory::getInstance(),
            GetConnectionOptions(),
            '`alunos`'
        );
        $this->dataset->addFields(
            array(
                new IntegerField('id', true, true, true),
                new StringField('nome', true),
                new StringField('matricula', true),
                new StringField('email', true),
                new DateField('dataNascimento', true),
                new StringField('cpfResponsavel', true)
            )
        );
    }

    protected function DoPrepare()
    {
    }

    protected function CreatePageNavigator()
    {
        $result = new CompositePageNavigator($this);

        $partitionNavigator = new PageNavigator('pnav', $this, $this->dataset);
        $partitionNavigator->SetRowsPerPage(20);
        $result->AddPageNavigator($partitionNavigator);

        return $result;
    }

    protected function CreateRssGenerator()
    {
        return null;
    }

    protected function setupCharts()
    {
    }

    protected function getFiltersColumns()
    {
        return array(
            new FilterColumn($this->dataset, 'id', 'id', 'Id'),
            new FilterColumn($this->dataset, 'nome', 'nome', 'Nome'),
            new FilterColumn($this->dataset, 'matricula', 'matricula', 'Matricula'),
            new FilterColumn($this->dataset, 'email', 'email', 'Email'),
            new FilterColumn($this->dataset, 'dataNascimento', 'dataNascimento', 'Data Nascimento'),
            new FilterColumn($this->dataset, 'cpfResponsavel', 'cpfResponsavel', 'Cpf Responsavel')
        );
    }

    protected function setupQuickFilter(QuickFilter $quickFilter, FixedKeysArray $columns)
    {
        $quickFilter
            ->addColumn($columns['id'])
            ->addColumn($columns['nome'])
            ->addColumn($columns['matricula'])
            ->addColumn($columns['email'])
            ->addColumn($columns['dataNascimento'])
            ->addColumn($columns['cpfResponsavel']);
    }

    protected function setupColumnFilter(ColumnFilter $columnFilter)
    {
    }

    protected function setupFilterBuilder(FilterBuilder $filterBuilder, FixedKeysArray $columns)
    {
    }

    protected function AddOperationsColumns(Grid $grid)
    {
        $actions = $grid->getActions();
        $actions->setCaption($this->GetLocalizerCaptions()->GetMessageString('Actions'));
        $actions->setPosition(ActionList::POSITION_LEFT);

        if ($this->GetSecurityInfo()->HasViewGrant()) {
            $operation = new LinkOperation($this->GetLocalizerCaptions()->GetMessageString('View'), OPERATION_VIEW, $this->dataset, $grid);
            $operation->setUseImage(true);
            $actions->addOperation($operation);
        }

        if ($this->GetSecurityInfo()->HasEditGrant()) {
            $operation = new LinkOperation($this->GetLocalizerCaptions()->GetMessageString('Edit'), OPERATION_EDIT, $this->dataset, $grid);
            $operation->setUseImage(true);
            $actions->addOperation($operation);
            $operation->OnShow->AddListener('ShowEditButtonHandler', $this);
        }

        if ($this->deleteOperationIsAllowed()) {
            $operation = new AjaxOperation(
                OPERATION_DELETE,
                $this->GetLocalizerCaptions()->GetMessageString('Delete'),
                $this->GetLocalizerCaptions()->GetMessageString('Delete'),
                $this->dataset,
                $this->GetModalGridDeleteHandler(),
                $grid
            );
            $operation->setUseImage(true);
            $actions->addOperation($operation);
            $operation->OnShow->AddListener('ShowDeleteButtonHandler', $this);
        }


        if ($this->GetSecurityInfo()->HasAddGrant()) {
            $operation = new LinkOperation($this->GetLocalizerCaptions()->GetMessageString('Copy'), OPERATION_COPY, $this->dataset, $grid);
            $operation->setUseImage(true);
            $actions->addOperation($operation);
        }
    }

    protected function AddFieldColumns(Grid $grid, $withDetails = true)
    {
        //
        // View column for id field
        //
        $column = new NumberViewColumn('id', 'id', 'Id', $this->dataset);
        $column->SetOrderable(true);
        $column->setNumberAfterDecimal(0);
        $column->setThousandsSeparator(',');
        $column->setDecimalSeparator('');
        $column->setMinimalVisibility(ColumnVisibility::PHONE);
        $grid->AddViewColumn($column);
        //
        // View column for nome field
        //
        $column = new TextViewColumn('nome', 'nome', 'Nome', $this->dataset);
        $column->SetOrderable(true);
        $column->setMinimalVisibility(ColumnVisibility::PHONE);
        $grid->AddViewColumn($column);
        //
        // View column for matricula field
        //
        $column = new TextViewColumn('matricula', 'matricula', 'Matricula', $this->dataset);
        $column->SetOrderable(true);
        $column->setMinimalVisibility(ColumnVisibility::PHONE);
        $grid->AddViewColumn($column);
        //
        // View column for email field
        //
        $column = new TextViewColumn('email', 'email', 'Email', $this->dataset);
        $column->SetOrderable(true);
        $column->setMinimalVisibility(ColumnVisibility::PHONE);
        $grid->AddViewColumn($column);
        //
        // View column for dataNascimento field
        //
        $column = new DateTimeViewColumn('dataNascimento', 'dataNascimento', 'Data Nascimento', $this->dataset);
        $column->SetOrderable(true);
        $column->SetDateTimeFormat('Y-m-d');
        $column->setMinimalVisibility(ColumnVisibility::PHONE);
        $grid->AddViewColumn($column);
        //
        // View column for cpfResponsavel field
        //
        $column = new TextViewColumn('cpfResponsavel', 'cpfResponsavel', 'Cpf Responsavel', $this->dataset);
        $column->SetOrderable(true);
        $column->setMinimalVisibility(ColumnVisibility::PHONE);
        $grid->AddViewColumn($column);
    }

    protected function AddSingleRecordViewColumns(Grid $grid)
    {
        //
        // View column for id field
        //
        $column = new NumberViewColumn('id', 'id', 'Id', $this->dataset);
        $column->SetOrderable(true);
        $column->setNumberAfterDecimal(0);
        $column->setThousandsSeparator(',');
        $column->setDecimalSeparator('');
        $grid->AddSingleRecordViewColumn($column);

        //
        // View column for nome field
        //
        $column = new TextViewColumn('nome', 'nome', 'Nome', $this->dataset);
        $column->SetOrderable(true);
        $grid->AddSingleRecordViewColumn($column);

        //
        // View column for matricula field
        //
        $column = new TextViewColumn('matricula', 'matricula', 'Matricula', $this->dataset);
        $column->SetOrderable(true);
        $grid->AddSingleRecordViewColumn($column);

        //
        // View column for email field
        //
        $column = new TextViewColumn('email', 'email', 'Email', $this->dataset);
        $column->SetOrderable(true);
        $grid->AddSingleRecordViewColumn($column);

        //
        // View column for dataNascimento field
        //
        $column = new DateTimeViewColumn('dataNascimento', 'dataNascimento', 'Data Nascimento', $this->dataset);
        $column->SetOrderable(true);
        $column->SetDateTimeFormat('Y-m-d');
        $grid->AddSingleRecordViewColumn($column);

        //
        // View column for cpfResponsavel field
        //
        $column = new TextViewColumn('cpfResponsavel', 'cpfResponsavel', 'Cpf Responsavel', $this->dataset);
        $column->SetOrderable(true);
        $grid->AddSingleRecordViewColumn($column);
    }

    protected function AddEditColumns(Grid $grid)
    {
        //
        // Edit column for nome field
        //
        $editor = new TextEdit('nome_edit');
        $editor->SetMaxLength(70);
        $editColumn = new CustomEditColumn('Nome', 'nome', $editor, $this->dataset);
        $validator = new RequiredValidator(StringUtils::Format($this->GetLocalizerCaptions()->GetMessageString('RequiredValidationMessage'), $editColumn->GetCaption()));
        $editor->GetValidatorCollection()->AddValidator($validator);
        $editColumn->setAllowListCellEdit(false);
        $editColumn->setAllowSingleViewCellEdit(false);
        $this->ApplyCommonColumnEditProperties($editColumn);
        $grid->AddEditColumn($editColumn);

        //
        // Edit column for matricula field
        //
        $editor = new TextEdit('matricula_edit');
        $editor->SetMaxLength(30);
        $editColumn = new CustomEditColumn('Matricula', 'matricula', $editor, $this->dataset);
        $validator = new RequiredValidator(StringUtils::Format($this->GetLocalizerCaptions()->GetMessageString('RequiredValidationMessage'), $editColumn->GetCaption()));
        $editor->GetValidatorCollection()->AddValidator($validator);
        $editColumn->setAllowListCellEdit(false);
        $editColumn->setAllowSingleViewCellEdit(false);
        $this->ApplyCommonColumnEditProperties($editColumn);
        $grid->AddEditColumn($editColumn);

        //
        // Edit column for email field
        //
        $editor = new TextEdit('email_edit');
        $editor->SetMaxLength(50);
        $editColumn = new CustomEditColumn('Email', 'email', $editor, $this->dataset);
        $validator = new RequiredValidator(StringUtils::Format($this->GetLocalizerCaptions()->GetMessageString('RequiredValidationMessage'), $editColumn->GetCaption()));
        $editor->GetValidatorCollection()->AddValidator($validator);
        $editColumn->setAllowListCellEdit(false);
        $editColumn->setAllowSingleViewCellEdit(false);
        $this->ApplyCommonColumnEditProperties($editColumn);
        $grid->AddEditColumn($editColumn);

        //
        // Edit column for dataNascimento field
        //
        $editor = new DateTimeEdit('datanascimento_edit', false, 'Y-m-d');
        $editColumn = new CustomEditColumn('Data Nascimento', 'dataNascimento', $editor, $this->dataset);
        $validator = new RequiredValidator(StringUtils::Format($this->GetLocalizerCaptions()->GetMessageString('RequiredValidationMessage'), $editColumn->GetCaption()));
        $editor->GetValidatorCollection()->AddValidator($validator);
        $editColumn->setAllowListCellEdit(false);
        $editColumn->setAllowSingleViewCellEdit(false);
        $this->ApplyCommonColumnEditProperties($editColumn);
        $grid->AddEditColumn($editColumn);

        //
        // Edit column for cpfResponsavel field
        //
        $editor = new TextEdit('cpfresponsavel_edit');
        $editor->SetMaxLength(14);
        $editColumn = new CustomEditColumn('Cpf Responsavel', 'cpfResponsavel', $editor, $this->dataset);
        $validator = new RequiredValidator(StringUtils::Format($this->GetLocalizerCaptions()->GetMessageString('RequiredValidationMessage'), $editColumn->GetCaption()));
        $editor->GetValidatorCollection()->AddValidator($validator);
        $editColumn->setAllowListCellEdit(false);
        $editColumn->setAllowSingleViewCellEdit(false);
        $this->ApplyCommonColumnEditProperties($editColumn);
        $grid->AddEditColumn($editColumn);
    }

    protected function AddMultiEditColumns(Grid $grid)
    {
        //
        // Edit column for nome field
        //
        $editor = new TextEdit('nome_edit');
        $editor->SetMaxLength(70);
        $editColumn = new CustomEditColumn('Nome', 'nome', $editor, $this->dataset);
        $validator = new RequiredValidator(StringUtils::Format($this->GetLocalizerCaptions()->GetMessageString('RequiredValidationMessage'), $editColumn->GetCaption()));
        $editor->GetValidatorCollection()->AddValidator($validator);
        $this->ApplyCommonColumnEditProperties($editColumn);
        $grid->AddMultiEditColumn($editColumn);

        //
        // Edit column for matricula field
        //
        $editor = new TextEdit('matricula_edit');
        $editor->SetMaxLength(30);
        $editColumn = new CustomEditColumn('Matricula', 'matricula', $editor, $this->dataset);
        $validator = new RequiredValidator(StringUtils::Format($this->GetLocalizerCaptions()->GetMessageString('RequiredValidationMessage'), $editColumn->GetCaption()));
        $editor->GetValidatorCollection()->AddValidator($validator);
        $this->ApplyCommonColumnEditProperties($editColumn);
        $grid->AddMultiEditColumn($editColumn);

        //
        // Edit column for email field
        //
        $editor = new TextEdit('email_edit');
        $editor->SetMaxLength(50);
        $editColumn = new CustomEditColumn('Email', 'email', $editor, $this->dataset);
        $validator = new RequiredValidator(StringUtils::Format($this->GetLocalizerCaptions()->GetMessageString('RequiredValidationMessage'), $editColumn->GetCaption()));
        $editor->GetValidatorCollection()->AddValidator($validator);
        $this->ApplyCommonColumnEditProperties($editColumn);
        $grid->AddMultiEditColumn($editColumn);

        //
        // Edit column for dataNascimento field
        //
        $editor = new DateTimeEdit('datanascimento_edit', false, 'Y-m-d');
        $editColumn = new CustomEditColumn('Data Nascimento', 'dataNascimento', $editor, $this->dataset);
        $validator = new RequiredValidator(StringUtils::Format($this->GetLocalizerCaptions()->GetMessageString('RequiredValidationMessage'), $editColumn->GetCaption()));
        $editor->GetValidatorCollection()->AddValidator($validator);
        $this->ApplyCommonColumnEditProperties($editColumn);
        $grid->AddMultiEditColumn($editColumn);

        //
        // Edit column for cpfResponsavel field
        //
        $editor = new TextEdit('cpfresponsavel_edit');
        $editor->SetMaxLength(14);
        $editColumn = new CustomEditColumn('Cpf Responsavel', 'cpfResponsavel', $editor, $this->dataset);
        $validator = new RequiredValidator(StringUtils::Format($this->GetLocalizerCaptions()->GetMessageString('RequiredValidationMessage'), $editColumn->GetCaption()));
        $editor->GetValidatorCollection()->AddValidator($validator);
        $this->ApplyCommonColumnEditProperties($editColumn);
        $grid->AddMultiEditColumn($editColumn);
    }

    protected function AddToggleEditColumns(Grid $grid)
    {
    }

    protected function AddInsertColumns(Grid $grid)
    {
        //
        // Edit column for nome field
        //
        $editor = new TextEdit('nome_edit');
        $editor->SetMaxLength(70);
        $editColumn = new CustomEditColumn('Nome', 'nome', $editor, $this->dataset);
        $validator = new RequiredValidator(StringUtils::Format($this->GetLocalizerCaptions()->GetMessageString('RequiredValidationMessage'), $editColumn->GetCaption()));
        $editor->GetValidatorCollection()->AddValidator($validator);
        $this->ApplyCommonColumnEditProperties($editColumn);
        $grid->AddInsertColumn($editColumn);

        //
        // Edit column for matricula field
        //
        $editor = new TextEdit('matricula_edit');
        $editor->SetMaxLength(30);
        $editColumn = new CustomEditColumn('Matricula', 'matricula', $editor, $this->dataset);
        $validator = new RequiredValidator(StringUtils::Format($this->GetLocalizerCaptions()->GetMessageString('RequiredValidationMessage'), $editColumn->GetCaption()));
        $editor->GetValidatorCollection()->AddValidator($validator);
        $this->ApplyCommonColumnEditProperties($editColumn);
        $grid->AddInsertColumn($editColumn);

        //
        // Edit column for email field
        //
        $editor = new TextEdit('email_edit');
        $editor->SetMaxLength(50);
        $editColumn = new CustomEditColumn('Email', 'email', $editor, $this->dataset);
        $validator = new RequiredValidator(StringUtils::Format($this->GetLocalizerCaptions()->GetMessageString('RequiredValidationMessage'), $editColumn->GetCaption()));
        $editor->GetValidatorCollection()->AddValidator($validator);
        $this->ApplyCommonColumnEditProperties($editColumn);
        $grid->AddInsertColumn($editColumn);

        //
        // Edit column for dataNascimento field
        //
        $editor = new DateTimeEdit('datanascimento_edit', false, 'Y-m-d');
        $editColumn = new CustomEditColumn('Data Nascimento', 'dataNascimento', $editor, $this->dataset);
        $validator = new RequiredValidator(StringUtils::Format($this->GetLocalizerCaptions()->GetMessageString('RequiredValidationMessage'), $editColumn->GetCaption()));
        $editor->GetValidatorCollection()->AddValidator($validator);
        $this->ApplyCommonColumnEditProperties($editColumn);
        $grid->AddInsertColumn($editColumn);

        //
        // Edit column for cpfResponsavel field
        //
        $editor = new TextEdit('cpfresponsavel_edit');
        $editor->SetMaxLength(14);
        $editColumn = new CustomEditColumn('Cpf Responsavel', 'cpfResponsavel', $editor, $this->dataset);
        $validator = new RequiredValidator(StringUtils::Format($this->GetLocalizerCaptions()->GetMessageString('RequiredValidationMessage'), $editColumn->GetCaption()));
        $editor->GetValidatorCollection()->AddValidator($validator);
        $this->ApplyCommonColumnEditProperties($editColumn);
        $grid->AddInsertColumn($editColumn);
        $grid->SetShowAddButton(true && $this->GetSecurityInfo()->HasAddGrant());
    }

    private function AddMultiUploadColumn(Grid $grid)
    {
    }

    protected function AddPrintColumns(Grid $grid)
    {
        //
        // View column for id field
        //
        $column = new NumberViewColumn('id', 'id', 'Id', $this->dataset);
        $column->SetOrderable(true);
        $column->setNumberAfterDecimal(0);
        $column->setThousandsSeparator(',');
        $column->setDecimalSeparator('');
        $grid->AddPrintColumn($column);

        //
        // View column for nome field
        //
        $column = new TextViewColumn('nome', 'nome', 'Nome', $this->dataset);
        $column->SetOrderable(true);
        $grid->AddPrintColumn($column);

        //
        // View column for matricula field
        //
        $column = new TextViewColumn('matricula', 'matricula', 'Matricula', $this->dataset);
        $column->SetOrderable(true);
        $grid->AddPrintColumn($column);

        //
        // View column for email field
        //
        $column = new TextViewColumn('email', 'email', 'Email', $this->dataset);
        $column->SetOrderable(true);
        $grid->AddPrintColumn($column);

        //
        // View column for dataNascimento field
        //
        $column = new DateTimeViewColumn('dataNascimento', 'dataNascimento', 'Data Nascimento', $this->dataset);
        $column->SetOrderable(true);
        $column->SetDateTimeFormat('Y-m-d');
        $grid->AddPrintColumn($column);

        //
        // View column for cpfResponsavel field
        //
        $column = new TextViewColumn('cpfResponsavel', 'cpfResponsavel', 'Cpf Responsavel', $this->dataset);
        $column->SetOrderable(true);
        $grid->AddPrintColumn($column);
    }

    protected function AddExportColumns(Grid $grid)
    {
        //
        // View column for id field
        //
        $column = new NumberViewColumn('id', 'id', 'Id', $this->dataset);
        $column->SetOrderable(true);
        $column->setNumberAfterDecimal(0);
        $column->setThousandsSeparator(',');
        $column->setDecimalSeparator('');
        $grid->AddExportColumn($column);

        //
        // View column for nome field
        //
        $column = new TextViewColumn('nome', 'nome', 'Nome', $this->dataset);
        $column->SetOrderable(true);
        $grid->AddExportColumn($column);

        //
        // View column for matricula field
        //
        $column = new TextViewColumn('matricula', 'matricula', 'Matricula', $this->dataset);
        $column->SetOrderable(true);
        $grid->AddExportColumn($column);

        //
        // View column for email field
        //
        $column = new TextViewColumn('email', 'email', 'Email', $this->dataset);
        $column->SetOrderable(true);
        $grid->AddExportColumn($column);

        //
        // View column for dataNascimento field
        //
        $column = new DateTimeViewColumn('dataNascimento', 'dataNascimento', 'Data Nascimento', $this->dataset);
        $column->SetOrderable(true);
        $column->SetDateTimeFormat('Y-m-d');
        $grid->AddExportColumn($column);

        //
        // View column for cpfResponsavel field
        //
        $column = new TextViewColumn('cpfResponsavel', 'cpfResponsavel', 'Cpf Responsavel', $this->dataset);
        $column->SetOrderable(true);
        $grid->AddExportColumn($column);
    }

    private function AddCompareColumns(Grid $grid)
    {
        //
        // View column for nome field
        //
        $column = new TextViewColumn('nome', 'nome', 'Nome', $this->dataset);
        $column->SetOrderable(true);
        $grid->AddCompareColumn($column);

        //
        // View column for matricula field
        //
        $column = new TextViewColumn('matricula', 'matricula', 'Matricula', $this->dataset);
        $column->SetOrderable(true);
        $grid->AddCompareColumn($column);

        //
        // View column for email field
        //
        $column = new TextViewColumn('email', 'email', 'Email', $this->dataset);
        $column->SetOrderable(true);
        $grid->AddCompareColumn($column);

        //
        // View column for dataNascimento field
        //
        $column = new DateTimeViewColumn('dataNascimento', 'dataNascimento', 'Data Nascimento', $this->dataset);
        $column->SetOrderable(true);
        $column->SetDateTimeFormat('Y-m-d');
        $grid->AddCompareColumn($column);

        //
        // View column for cpfResponsavel field
        //
        $column = new TextViewColumn('cpfResponsavel', 'cpfResponsavel', 'Cpf Responsavel', $this->dataset);
        $column->SetOrderable(true);
        $grid->AddCompareColumn($column);
    }

    private function AddCompareHeaderColumns(Grid $grid)
    {
    }

    public function GetPageDirection()
    {
        return null;
    }

    public function isFilterConditionRequired()
    {
        return false;
    }

    protected function ApplyCommonColumnEditProperties(CustomEditColumn $column)
    {
        $column->SetDisplaySetToNullCheckBox(false);
        $column->SetDisplaySetToDefaultCheckBox(false);
        $column->SetVariableContainer($this->GetColumnVariableContainer());
    }

    function GetCustomClientScript()
    {
        return;
    }

    function GetOnPageLoadedClientScript()
    {
        return;
    }

    protected function CreateGrid()
    {
        $result = new Grid($this, $this->dataset);
        if ($this->GetSecurityInfo()->HasDeleteGrant())
            $result->SetAllowDeleteSelected(false);
        else
            $result->SetAllowDeleteSelected(false);

        ApplyCommonPageSettings($this, $result);

        $result->SetUseImagesForActions(true);
        $result->SetUseFixedHeader(false);
        $result->SetShowLineNumbers(false);
        $result->SetShowKeyColumnsImagesInHeader(false);
        $result->setAllowSortingByDialog(false);
        $result->SetViewMode(ViewMode::TABLE);
        $result->setEnableRuntimeCustomization(false);
        $result->setAllowAddMultipleRecords(false);
        $result->setMultiEditAllowed($this->GetSecurityInfo()->HasEditGrant() && false);
        $result->setTableBordered(false);
        $result->setTableCondensed(false);

        $result->SetHighlightRowAtHover(false);
        $result->SetWidth('');
        $this->AddOperationsColumns($result);
        $this->AddFieldColumns($result);
        $this->AddSingleRecordViewColumns($result);
        $this->AddEditColumns($result);
        $this->AddMultiEditColumns($result);
        $this->AddToggleEditColumns($result);
        $this->AddInsertColumns($result);
        $this->AddPrintColumns($result);
        $this->AddExportColumns($result);
        $this->AddMultiUploadColumn($result);


        $this->SetShowPageList(true);
        $this->SetShowTopPageNavigator(true);
        $this->SetShowBottomPageNavigator(true);
        $this->setAllowedActions(array('view', 'insert', 'copy', 'edit', 'delete'));
        $this->setPrintListAvailable(false);
        $this->setPrintListRecordAvailable(false);
        $this->setPrintOneRecordAvailable(false);
        $this->setAllowPrintSelectedRecords(false);
        $this->setOpenPrintFormInNewTab(false);
        $this->setExportListAvailable(array());
        $this->setExportSelectedRecordsAvailable(array());
        $this->setExportListRecordAvailable(array());
        $this->setExportOneRecordAvailable(array());
        $this->setOpenExportedPdfInNewTab(false);

        return $result;
    }

    protected function setClientSideEvents(Grid $grid)
    {
    }

    protected function doRegisterHandlers()
    {
    }

    protected function doCustomRenderColumn($fieldName, $fieldData, $rowData, &$customText, &$handled)
    {
    }

    protected function doCustomRenderPrintColumn($fieldName, $fieldData, $rowData, &$customText, &$handled)
    {
    }

    protected function doCustomRenderExportColumn($exportType, $fieldName, $fieldData, $rowData, &$customText, &$handled)
    {
    }

    protected function doCustomDrawRow($rowData, &$cellFontColor, &$cellFontSize, &$cellBgColor, &$cellItalicAttr, &$cellBoldAttr)
    {
    }

    protected function doExtendedCustomDrawRow($rowData, &$rowCellStyles, &$rowStyles, &$rowClasses, &$cellClasses)
    {
    }

    protected function doCustomRenderTotal($totalValue, $aggregate, $columnName, &$customText, &$handled)
    {
    }

    protected function doCustomDefaultValues(&$values, &$handled)
    {
    }

    protected function doCustomCompareColumn($columnName, $valueA, $valueB, &$result)
    {
    }

    protected function doBeforeInsertRecord($page, &$rowData, $tableName, &$cancel, &$message, &$messageDisplayTime)
    {
    }

    protected function doBeforeUpdateRecord($page, $oldRowData, &$rowData, $tableName, &$cancel, &$message, &$messageDisplayTime)
    {
    }

    protected function doBeforeDeleteRecord($page, &$rowData, $tableName, &$cancel, &$message, &$messageDisplayTime)
    {
    }

    protected function doAfterInsertRecord($page, $rowData, $tableName, &$success, &$message, &$messageDisplayTime)
    {
    }

    protected function doAfterUpdateRecord($page, $oldRowData, $rowData, $tableName, &$success, &$message, &$messageDisplayTime)
    {
    }

    protected function doAfterDeleteRecord($page, $rowData, $tableName, &$success, &$message, &$messageDisplayTime)
    {
    }

    protected function doCustomHTMLHeader($page, &$customHtmlHeaderText)
    {
    }

    protected function doGetCustomTemplate($type, $part, $mode, &$result, &$params)
    {
    }

    protected function doGetCustomExportOptions(Page $page, $exportType, $rowData, &$options)
    {
    }

    protected function doFileUpload($fieldName, $rowData, &$result, &$accept, $originalFileName, $originalFileExtension, $fileSize, $tempFileName)
    {
    }

    protected function doPrepareChart(Chart $chart)
    {
    }

    protected function doPrepareColumnFilter(ColumnFilter $columnFilter)
    {
    }

    protected function doPrepareFilterBuilder(FilterBuilder $filterBuilder, FixedKeysArray $columns)
    {
    }

    protected function doGetSelectionFilters(FixedKeysArray $columns, &$result)
    {
    }

    protected function doGetCustomFormLayout($mode, FixedKeysArray $columns, FormLayout $layout)
    {
    }

    protected function doGetCustomColumnGroup(FixedKeysArray $columns, ViewColumnGroup $columnGroup)
    {
    }

    protected function doPageLoaded()
    {
    }

    protected function doCalculateFields($rowData, $fieldName, &$value)
    {
    }

    protected function doGetCustomRecordPermissions(Page $page, &$usingCondition, $rowData, &$allowEdit, &$allowDelete, &$mergeWithDefault, &$handled)
    {
    }

    protected function doAddEnvironmentVariables(Page $page, &$variables)
    {
    }
}

SetUpUserAuthorization();

try {
    $Page = new alunosPage("alunos", "alunos.php", GetCurrentUserPermissionsForPage("alunos"), 'UTF-8');
    $Page->SetRecordPermission(GetCurrentUserRecordPermissionsForDataSource("alunos"));
    GetApplication()->SetMainPage($Page);
    GetApplication()->Run();
} catch (Exception $e) {
    ShowErrorPage($e);
}
