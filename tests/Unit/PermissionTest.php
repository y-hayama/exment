<?php

namespace Exceedone\Exment\Tests\Unit;

use Exceedone\Exment\Model\LoginUser;
use Exceedone\Exment\Model\CustomTable;
use Exceedone\Exment\Model\System;
use Exceedone\Exment\Enums\JoinedOrgFilterType;

class PermissionTest extends UnitTestBase
{
    protected function init(){
        System::clearCache();
    }

    // Organization -------------------------------------------
    public function testOrganizationCompany()
    {
        $this->executeTestOrganization(5, JoinedOrgFilterType::ONLY_JOIN, [1], true);
    }

    public function testOrganizationDev()
    {
        $this->executeTestOrganization(6, JoinedOrgFilterType::ONLY_JOIN, [2], true);
    }

    public function testOrganizationDev1()
    {
        $this->executeTestOrganization(7, JoinedOrgFilterType::ONLY_JOIN, [4], true);
    }

    public function testOrganizationCompanyUpper()
    {
        $this->executeTestOrganization(5, JoinedOrgFilterType::ONLY_UPPER, [1, 2, 3, 4, 5], true);
    }

    public function testOrganizationDevUpper()
    {
        $this->executeTestOrganization(6, JoinedOrgFilterType::ONLY_UPPER, [2, 4, 5], true);
    }

    public function testOrganizationDev1Upper()
    {
        $this->executeTestOrganization(7, JoinedOrgFilterType::ONLY_UPPER, [4], true);
    }

    public function testOrganizationCompanyDowner()
    {
        $this->executeTestOrganization(5, JoinedOrgFilterType::ONLY_DOWNER, [1], true);
    }

    public function testOrganizationDevDowner()
    {
        $this->executeTestOrganization(6, JoinedOrgFilterType::ONLY_DOWNER, [1, 2], true);
    }

    public function testOrganizationDev1Downer()
    {
        $this->executeTestOrganization(7, JoinedOrgFilterType::ONLY_DOWNER, [1, 2, 4], true);
    }

    public function testOrganizationCompanyAll()
    {
        $this->executeTestOrganization(5, JoinedOrgFilterType::ALL, [1, 2, 3, 4, 5], true);
    }

    public function testOrganizationDevAll()
    {
        $this->executeTestOrganization(6, JoinedOrgFilterType::ALL, [1, 2, 4, 5], true);
    }

    public function testOrganizationDev1All()
    {
        $this->executeTestOrganization(7, JoinedOrgFilterType::ALL, [1, 2, 4], true);
    }


    // Role Group -------------------------------------------
    public function testRoleGroupAdmin()
    {
        $this->executeTestRoleGroup(1, JoinedOrgFilterType::ALL, true);
    }

    public function testRoleGroupDirect()
    {
        $this->executeTestRoleGroup(6, JoinedOrgFilterType::ALL, true);
    }

    public function testRoleGroupUpper()
    {
        $this->executeTestRoleGroup(5, JoinedOrgFilterType::ALL, true);
    }

    public function testRoleGroupDowner()
    {
        $this->executeTestRoleGroup(7, JoinedOrgFilterType::ALL, true);
    }

    public function testRoleGroupOtherOrg()
    {
        $this->executeTestRoleGroup(10, JoinedOrgFilterType::ALL, false);
    }

    public function testRoleGroupDirectOnlyUpper()
    {
        $this->executeTestRoleGroup(6, JoinedOrgFilterType::ONLY_UPPER, true);
    }

    public function testRoleGroupUpperOnlyUpper()
    {
        $this->executeTestRoleGroup(5, JoinedOrgFilterType::ONLY_UPPER, true);
    }

    public function testRoleGroupDownerOnlyUpper()
    {
        $this->executeTestRoleGroup(7, JoinedOrgFilterType::ONLY_UPPER, false);
    }

    public function testRoleGroupOtherOrgOnlyUpper()
    {
        $this->executeTestRoleGroup(10, JoinedOrgFilterType::ONLY_UPPER, false);
    }
    
    public function testRoleGroupDirectOnlyDowner()
    {
        $this->executeTestRoleGroup(6, JoinedOrgFilterType::ONLY_DOWNER, true);
    }

    public function testRoleGroupUpperOnlyDowner()
    {
        $this->executeTestRoleGroup(5, JoinedOrgFilterType::ONLY_DOWNER, false);
    }

    public function testRoleGroupDownerOnlyDowner()
    {
        $this->executeTestRoleGroup(7, JoinedOrgFilterType::ONLY_DOWNER, true);
    }

    public function testRoleGroupOtherOrgOnlyDowner()
    {
        $this->executeTestRoleGroup(10, JoinedOrgFilterType::ONLY_DOWNER, false);
    }
    
    public function testRoleGroupDirectOnlyJoin()
    {
        $this->executeTestRoleGroup(6, JoinedOrgFilterType::ONLY_JOIN, true);
    }

    public function testRoleGroupUpperOnlyJoin()
    {
        $this->executeTestRoleGroup(5, JoinedOrgFilterType::ONLY_JOIN, false);
    }

    public function testRoleGroupDownerOnlyJoin()
    {
        $this->executeTestRoleGroup(7, JoinedOrgFilterType::ONLY_JOIN, false);
    }

    public function testRoleGroupOtherOrgOnlyJoin()
    {
        $this->executeTestRoleGroup(10, JoinedOrgFilterType::ONLY_JOIN, false);
    }


    // Custom Value -------------------------------------------
    public function testCustomValueAdmin()
    {
        $this->executeTestCustomValue(1, JoinedOrgFilterType::ONLY_JOIN, true);
    }

    public function testCustomValueDirect()
    {
        $this->executeTestCustomValue(6, JoinedOrgFilterType::ONLY_JOIN, true);
    }

    public function testCustomValueUpper()
    {
        $this->executeTestCustomValue(5, JoinedOrgFilterType::ONLY_JOIN, false);
    }

    public function testCustomValueDowner()
    {
        $this->executeTestCustomValue(7, JoinedOrgFilterType::ONLY_JOIN, false);
    }

    public function testCustomValueOtherOrg()
    {
        $this->executeTestCustomValue(10, JoinedOrgFilterType::ONLY_JOIN, false);
    }


    public function testCustomValueDirectOnlyUpper()
    {
        $this->executeTestCustomValue(6, JoinedOrgFilterType::ONLY_UPPER, true);
    }

    public function testCustomValueUpperOnlyUpper()
    {
        $this->executeTestCustomValue(5, JoinedOrgFilterType::ONLY_UPPER, true);
    }

    public function testCustomValueDownerOnlyUpper()
    {
        $this->executeTestCustomValue(7, JoinedOrgFilterType::ONLY_UPPER, false);
    }

    public function testCustomValueOtherOrgOnlyUpper()
    {
        $this->executeTestCustomValue(10, JoinedOrgFilterType::ONLY_UPPER, false);
    }
    
    
    public function testCustomValueDirectOnlyDowner()
    {
        $this->executeTestCustomValue(6, JoinedOrgFilterType::ONLY_DOWNER, true);
    }

    public function testCustomValueUpperOnlyDowner()
    {
        $this->executeTestCustomValue(5, JoinedOrgFilterType::ONLY_DOWNER, false);
    }

    public function testCustomValueDownerOnlyDowner()
    {
        $this->executeTestCustomValue(7, JoinedOrgFilterType::ONLY_DOWNER, true);
    }

    public function testCustomValueOtherOrgOnlyDowner()
    {
        $this->executeTestCustomValue(10, JoinedOrgFilterType::ONLY_DOWNER, false);
    }
    
    
    public function testCustomValueDirectAll()
    {
        $this->executeTestCustomValue(6, JoinedOrgFilterType::ALL, true);
    }

    public function testCustomValueUpperAll()
    {
        $this->executeTestCustomValue(5, JoinedOrgFilterType::ALL, true);
    }

    public function testCustomValueDownerAll()
    {
        $this->executeTestCustomValue(7, JoinedOrgFilterType::ALL, true);
    }

    public function testCustomValueOtherOrgAll()
    {
        $this->executeTestCustomValue(10, JoinedOrgFilterType::ALL, false);
    }


    protected function executeTestOrganization($loginId, $joinedOrgFilterType, $antiOrganizations, bool $antiResult){
        $this->init();

        $user = CustomTable::getEloquent('user')->getValueModel($loginId);
        $organizations = $user->getOrganizationIds($joinedOrgFilterType);
        
        sort($organizations);
        sort($antiOrganizations);
        
        $result = true;
        if(count($organizations) != count($antiOrganizations)){
            $result = false;
        }
        else{

            for($i = 0; $i < count($organizations); $i++){
                if($organizations[$i] != $antiOrganizations[$i]){
                    $result = false;
                    break;
                }
            }
        }

        $func = $antiResult ? 'assertTrue' : 'assertFalse';
        $this->{$func}(
            $result
        );
    }

    protected function executeTestRoleGroup($loginId, $joinedOrgFilterType, bool $result){
        $this->init();
        $this->be(LoginUser::find($loginId));
        System::org_joined_type_role_group($joinedOrgFilterType);
        
        $func = $result ? 'assertTrue' : 'assertFalse';
        $this->{$func}(CustomTable::getEloquent('roletest_custom_value_edit')->hasPermission());
    }

    protected function executeTestCustomValue($loginId, $joinedOrgFilterType, bool $result){
        $this->init();
        $this->be(LoginUser::find($loginId));
        System::org_joined_type_custom_value($joinedOrgFilterType);
        
        $func = $result ? 'assertTrue' : 'assertFalse';
        $custom_value = CustomTable::getEloquent('roletest_custom_value_edit')->getValueModel()->find(6); // created by dev user
        $this->{$func}(isset($custom_value));
    }
}
