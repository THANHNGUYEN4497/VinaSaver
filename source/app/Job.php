<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

use App\JobDynamoDB;

class Job extends Model
{
    protected $table = 'jobs';
    protected $dateFormat = 'U';
    protected $fillable = [
        'company_id ', 'staff_id ', ' title', 'category_id', 'job_category', 'salary', 'traffic', 'introduction_title', 'introduction_content', 'store_name', 'description', 'hours', 'working_time', 'welcome', 'requirements', 'treatment',   'release_start_date', 'release_end_date', 'management_staff', 'job_type_id', 'job_content', 'workplace_status', 'address', 'area_id', 'age_min', 'age_max', 'gender_ratio'
    ];
    public static function getListJobs($page_limit, $page_number, $keyword, $category, $start_date, $end_date)
    {
        $page_number = ($page_number - 1) * $page_limit;
        $query = Job::select(
            'jobs.id',
            'jobs.company_id',
            'companies.company_name',
            'jobs.title',
            'jobs.category_id',
            'categories.category_name',
            'jobs.created_at',
            'jobs.address'
        );

        $result = $query->leftjoin('companies', function ($join) use ($keyword) {
            $join->on('companies.id', '=', 'jobs.company_id');
        });

        $result = $query->join('categories', function ($join) use ($category) {
            $join->on('categories.id', '=', 'jobs.category_id');
            if (!empty($category)) {
                $join->where('jobs.category_id', $category);
            }
        });
        
        if (!empty($keyword)) {
            $result->where(function ($query) use ($keyword) {
                $query->where('companies.company_name', 'like', '%' . $keyword . '%')
                    ->orWhere('jobs.title', 'like', '%' . $keyword . '%');
            });
        }

        if (!empty($start_date) && !empty($end_date)) {
            $end_date = $end_date + 86399;
            $result = $result->where('jobs.created_at', '>=', $start_date)
                            ->where('jobs.created_at', '<=', $end_date);
        }
        if (!empty($start_date) && empty($end_date)) {
            $result = $result->where('jobs.created_at', '>=', $start_date);
        }
        if (empty($start_date) && !empty($end_date)) {
            $end_date = $end_date + 86399;
            $result = $result->where('jobs.created_at', '<=', $end_date);
        }

        $data = array();
        $data['total_items'] = $result->count();

        $result = $result->orderBy('jobs.id', 'desc')
            ->offset($page_number)
            ->limit($page_limit)
            ->get();
        $data['data'] = $result;
        if ($data) {
            return $data;
        } else {
            return null;
        }
    }

    public static function getNewJobs($page_number, $page_limit)
    {
        $page_number = ($page_number - 1) * $page_limit;
        $query = Job::select(
            'jobs.id',
            'jobs.title',
            'jobs.job_content',
            'jobs.salary',
            'categories.category_name'
        );
        $result = $query->leftjoin('categories', 'categories.id', '=', 'jobs.category_id')
            ->where('jobs.release_start_date', '<=', now()->timestamp)
            ->where('jobs.release_end_date', '>=', now()->timestamp);

        $data = array();
        $data['total_items'] = $result->count();

        $result = $result->orderBy('jobs.id', 'desc')
            ->offset($page_number)
            ->limit($page_limit)
            ->get();
        $data['data'] = $result;
        if (count($data['data']) > 0) {
            for ($i = 0; $i < count($data['data']); $i++) {
                $data['data'][$i]['main_image'] = JobFile::getMainImageJob($data['data'][$i]['id']);
                $data['data'][$i]['base_path'] = Job::getBasePathJob();
            }
        }

        return $data;
    }

    public static function getDetailById($job_id)
    {
        $query = Job::select(
            'jobs.id',
            'jobs.company_id',
            'companies.company_name',
            'companies.phone_number',
            'companies.latitude',
            'companies.longitude',
            'companies.url',
            'jobs.staff_id',
            'staffs.username',
            'staff_m.username as management_staff_name',
            'jobs.title',
            'categories.category_name AS category',
            'job_categories.job_category_name AS job_category',
            'jobs.description',
            'jobs.traffic',
            'jobs.introduction_title',
            'jobs.introduction_content',
            'jobs.store_name',
            'jobs.hours',
            'jobs.working_time',
            'jobs.welcome',
            'jobs.requirements',
            'jobs.treatment',
            'jobs.release_start_date',
            'jobs.release_end_date',
            'jobs.management_staff',
            'jobs.workplace_status',
            'jobs.job_content',
            'job_types.type_name AS job_type',
            'areas.area_name AS area',
            'jobs.area_id',
            'jobs.job_type_id',
            'jobs.category_id',
            'jobs.job_category_id',
            'jobs.salary',
            'jobs.age_min',
            'jobs.age_max',
            'jobs.gender_ratio',
            'jobs.address'
        );
        $result = $query->leftjoin('staffs', 'staffs.id', '=', 'jobs.staff_id')
            ->leftjoin('companies', 'companies.id', '=', 'jobs.company_id')
            ->leftjoin('categories', 'categories.id', '=', 'jobs.category_id')
            ->leftjoin('job_categories', 'job_categories.id', '=', 'jobs.job_category_id')
            ->leftjoin('job_types', 'job_types.id', '=', 'jobs.job_type_id')
            ->leftjoin('areas', 'areas.id', '=', 'jobs.area_id')
            ->leftjoin('staffs as staff_m', 'staff_m.id', '=', 'jobs.management_staff')
            ->where('jobs.id', $job_id)
            ->first();
        return $result;
    }

    public static function checkJobPermission($job_id, $management_id, $staff_privilege, $company_id)
    {
        $job = Job::where('id', $job_id)
                ->where('company_id', $company_id);
        if ($staff_privilege == 2) {
            $job = $job->where('management_staff', $management_id);
        }
        $job = $job->first();
        if (!empty($job)) {
            return $job->id;
        }
        return false;
    }

    public static function getDetailByConnector($job_id, $connector_id)
    {
        $query = Job::select(
            'jobs.id',
            'jobs.company_id',
            'companies.company_name',
            'companies.phone_number',
            'companies.latitude',
            'companies.longitude',
            'companies.url',
            'jobs.staff_id',
            'staffs.username',
            'staff_m.username as management_staff_name',
            'jobs.title',
            'categories.category_name AS category',
            'job_categories.job_category_name AS job_category',
            'jobs.description',
            'jobs.traffic',
            'jobs.introduction_title',
            'jobs.introduction_content',
            'jobs.store_name',
            'jobs.hours',
            'jobs.working_time',
            'jobs.welcome',
            'jobs.requirements',
            'jobs.treatment',
            'jobs.release_start_date',
            'jobs.release_end_date',
            'jobs.management_staff',
            'jobs.workplace_status',
            'jobs.job_content',
            'job_types.type_name AS job_type',
            'areas.area_name AS area',
            'jobs.area_id',
            'jobs.job_type_id',
            'jobs.category_id',
            'jobs.job_category_id',
            'jobs.salary',
            'jobs.age_min',
            'jobs.age_max',
            'jobs.gender_ratio',
            'jobs.address',
            'favorites.is_favorite'
        );
        $result = $query->leftjoin('staffs', 'staffs.id', '=', 'jobs.staff_id')
            ->leftjoin('companies', 'companies.id', '=', 'jobs.company_id')
            ->leftjoin('categories', 'categories.id', '=', 'jobs.category_id')
            ->leftjoin('job_categories', 'job_categories.id', '=', 'jobs.job_category_id')
            ->leftjoin('job_types', 'job_types.id', '=', 'jobs.job_type_id')
            ->leftjoin('areas', 'areas.id', '=', 'jobs.area_id')
            ->leftjoin('staffs as staff_m', 'staff_m.id', '=', 'jobs.management_staff')
            ->leftJoin('favorites', function ($q) use ($connector_id) {
                $q->on('favorites.job_id', '=', 'jobs.id')
                        ->where('favorites.connector_id', '=', "$connector_id");
            })
            ->where('jobs.id', $job_id)
            ->first();
        if (!empty($result)) {
            $result->images = JobFile::getDetailImageJob($job_id);
            $result->video = JobFile::getDetailVideoJob($job_id);
        }
        return $result;
    }

    public static function deleteById($id)
    {
        $connector = Job::destroy($id);
        $result_dynamodb = JobDynamoDB::remove($id);
        if ($connector) {
            return true;
        } else {
            return false;
        }
    }

    public static function add($job_title, $job_company_id, $job_staff_id, $job_category_id, $job_job_category_id, $job_job_type_id, $job_area_id, $description, $job_address, $job_salary, $job_age_min, $job_gender_ratio, $job_age_max, $job_traffic, $job_introduction_title, $job_introduction_content, $job_content, $job_store_name, $job_workplace_status, $job_hours, $job_working_time, $job_welcome, $job_requirements, $job_treatment, $job_release_start_date, $job_release_end_date, $job_management_staff)
    {
        $job = new Job();

        $job->title = $job_title;
        $job->company_id = $job_company_id;
        $job->staff_id = $job_staff_id;
        $job->category_id = $job_category_id;
        $job->job_category_id = $job_job_category_id;
        $job->job_type_id = $job_job_type_id;
        $job->area_id = $job_area_id;
        $job->description = $description;
        $job->address = $job_address;
        $job->salary = $job_salary;
        $job->age_max = $job_age_max;
        $job->age_min = $job_age_min;
        $job->gender_ratio = $job_gender_ratio;
        $job->traffic = $job_traffic;
        $job->introduction_title = $job_introduction_title;
        $job->introduction_content = $job_introduction_content;
        $job->job_content = $job_content;
        $job->store_name = $job_store_name;
        $job->workplace_status = $job_workplace_status;
        $job->hours = $job_hours;
        $job->working_time = $job_working_time;
        $job->welcome = $job_welcome;
        $job->requirements = $job_requirements;
        $job->treatment = $job_treatment;
        $job->release_start_date = Carbon::parse("$job_release_start_date")->timestamp;
        $job->release_end_date = Carbon::parse("$job_release_end_date")->timestamp;
        $job->management_staff = $job_management_staff;
        $job->save();

        $job_dynamodb = new JobDynamoDB();
        $job_dynamodb->jobId = $job->id;
        $job_dynamodb->save();

        return $job->id;
    }

    public static function edit($job_id, $job_title, $job_category_id, $job_job_category_id, $job_job_type_id, $job_area_id, $description, $job_address, $job_salary, $job_age_min, $job_gender_ratio, $job_age_max, $job_traffic, $job_introduction_title, $job_introduction_content, $job_content, $job_store_name, $job_workplace_status, $job_hours, $job_working_time, $job_welcome, $job_requirements, $job_treatment, $job_release_start_date, $job_release_end_date, $job_management_staff)
    {
        $job = Job::find($job_id);
        if (!empty($job)) {
            $job->title = $job_title;
            $job->category_id = $job_category_id;
            $job->job_category_id = $job_job_category_id;
            $job->job_type_id = $job_job_type_id;
            $job->area_id = $job_area_id;
            $job->description = $description;
            $job->address = $job_address;
            $job->salary = $job_salary;
            $job->age_max = $job_age_max;
            $job->age_min = $job_age_min;
            $job->gender_ratio = $job_gender_ratio;
            $job->traffic = $job_traffic;
            $job->introduction_title = $job_introduction_title;
            $job->introduction_content = $job_introduction_content;
            $job->job_content = $job_content;
            $job->store_name = $job_store_name;
            $job->workplace_status = $job_workplace_status;
            $job->hours = $job_hours;
            $job->working_time = $job_working_time;
            $job->welcome = $job_welcome;
            $job->requirements = $job_requirements;
            $job->treatment = $job_treatment;
            $job->release_start_date = Carbon::parse("$job_release_start_date")->timestamp;
            $job->release_end_date = Carbon::parse("$job_release_end_date")->timestamp;
            $job->management_staff = $job_management_staff;
            $job->save();
        }
        return $job->id;
    }
    public static function checkExistJob($job_id)
    {
        $job = Job::find($job_id);
        if (empty($job)) {
            return false;
        }
        return true;
    }
    public static function getByStaff($staff_id, $privilege, $company_id, $page_limit, $page_number, $keyword, $category_id, $start_date, $end_date)
    {
        $jobs = Job::select(
            DB::raw('
                jobs.id,
                jobs.address,
                jobs.title,
                jobs.created_at,
                categories.category_name,
                job_categories.job_category_name as job_category_name,
                count(wc.job_id) as applicants
            ')
        )
            ->leftjoin('staffs', 'staffs.id', '=', 'jobs.staff_id')
            ->leftjoin('categories', 'categories.id', '=', 'jobs.category_id')
            ->leftjoin('job_categories', 'job_categories.id', '=', 'jobs.job_category_id')
            ->leftjoin('work_connections as wc', 'wc.job_id', '=', 'jobs.id')
            ->groupBy(DB::raw('jobs.id'))
            ->orderBy('jobs.id', 'DESC')
            ->where('jobs.company_id', $company_id)
            ->where('jobs.title', 'like', "%$keyword%");
        if ($category_id) {
            $jobs->where('categories.id', $category_id);
        }
        if ($start_date) {
            $start_date = Carbon::parse("$start_date 00:00:00")->timestamp;
            $jobs->where('jobs.created_at', '>=', $start_date);
        }
        if ($end_date) {
            $end_date = Carbon::parse("$end_date 23:59:59")->timestamp;
            $jobs->where('jobs.created_at', '<=', $end_date);
        }

        if ($privilege == 2) {
            $jobs->where('management_staff', $staff_id);
        }

        $jobs_count = Job::select('jobs.id')
                        ->joinSub($jobs, 'qjobs', function ($join) {
                            $join->on('jobs.id', '=', 'qjobs.id');
                        });
        $count = $jobs_count->count();

        $jobs->offset(($page_number - 1) * $page_limit)
            ->limit($page_limit);

        return array('total' => $count, 'data' => $jobs->get());
    }

    public static function getListJobsByCategoryId($connector_id, $category_id, $page_number, $page_limit)
    {
        $page_number = ($page_number - 1) * $page_limit;
        $result = Job::select(
            'jobs.id',
            'jobs.title',
            'jobs.category_id',
            'categories.category_name',
            'jobs.salary',
            'favorites.is_favorite'
        )
        ->leftjoin('categories', function ($join) {
            $join->on('jobs.category_id', '=', 'categories.id');
        })
        ->leftjoin('favorites', function ($join) use ($connector_id) {
            $join->on('favorites.job_id', '=', 'jobs.id');
            $join->where('favorites.connector_id', '=', $connector_id);
        })
        ->where('jobs.category_id', '=', $category_id)
        ->where('jobs.release_start_date', '<=', now()->timestamp)
        ->where('jobs.release_end_date', '>=', now()->timestamp);

        $data = array();
        $data['total_items'] = $result->count();

        $result = $result->orderBy('jobs.id', 'desc')
            ->offset($page_number)
            ->limit($page_limit)
            ->get();
        $data['data'] = $result;
        if (count($data['data']) > 0) {
            for ($i = 0; $i < count($data['data']); $i++) {
                $data['data'][$i]['main_image'] = JobFile::getMainImageJob($data['data'][$i]['id']);
                $data['data'][$i]['base_path'] = Job::getBasePathJob();
            }
        }


        return $data;
    }


    public static function getListJobsWithCategory($connector_id, $number_item)
    {
        $result = array();
        $categories = Category::getList();
        for ($i = 0; $i < count($categories); $i++) {
            if (isset($categories[$i]['id'])) {
                $ary_data = array();
                $ary_data['category_id'] = $categories[$i]['id'];
                $ary_data['category_name'] = $categories[$i]['category_name'];

                $jobs = Job::select(
                    'jobs.id',
                    'jobs.title',
                    'jobs.category_id',
                    'categories.category_name',
                    'jobs.salary',
                    'favorites.is_favorite'
                )
                ->leftjoin('categories', function ($join) {
                    $join->on('jobs.category_id', '=', 'categories.id');
                })
                ->leftjoin('favorites', function ($join) use ($connector_id) {
                    $join->on('favorites.job_id', '=', 'jobs.id');
                    $join->where('favorites.connector_id', '=', $connector_id);
                })
                ->where('jobs.category_id', '=', $categories[$i]['id'])
                ->where('jobs.release_start_date', '<=', now()->timestamp)
                ->where('jobs.release_end_date', '>=', now()->timestamp);

                $ary_data['total_items'] = $jobs->count();
                $ary_data['jobs'] = $jobs->orderBy('jobs.id', 'desc')
                                            ->limit($number_item)
                                            ->get();

                foreach ($ary_data['jobs'] as $key => $value) {
                    $ary_data['jobs'][$key]['main_image'] = JobFile::getMainImageJob($ary_data['jobs'][$key]['id']);
                    $ary_data['jobs'][$key]['base_path'] = Job::getBasePathJob();
                }

                $result[] =  $ary_data;
            }
        }

        return $result;
    }

    public static function getListJobsWithCondition($connector_id, $page_number, $page_limit, $area_id, $job_category_id, $age, $gender_ratio, $workplace_status, $keyword, $job_type_id, $category_id)
    {
        $page_number = ($page_number - 1) * $page_limit;
        $result = Job::select(
            'jobs.id',
            'jobs.title',
            'jobs.category_id',
            'categories.category_name',
            'jobs.salary',
            'favorites.is_favorite'
        )
        ->leftjoin('categories', function ($join) {
            $join->on('jobs.category_id', '=', 'categories.id');
        })
        ->leftjoin('favorites', function ($join) use ($connector_id) {
            $join->on('favorites.job_id', '=', 'jobs.id');
            $join->where('favorites.connector_id', '=', $connector_id);
        });
        if (isset($area_id)) {
            $result = $result->where('jobs.area_id', '=', $area_id);
        }
        if (isset($job_category_id)) {
            $result = $result->where('jobs.job_category_id', '=', $job_category_id);
        }
        if (isset($gender_ratio)) {
            $result = $result->where('jobs.gender_ratio', '=', $gender_ratio);
        }
        if (isset($workplace_status)) {
            $result = $result->where('jobs.workplace_status', '=', $workplace_status);
        }
        if (isset($age)) {
            $result = $result->where('jobs.age_min', '<=', $age)
                            ->where('jobs.age_max', '>=', $age);
        }
        if (isset($job_type_id)) {
            $result = $result->where('jobs.job_type_id', '=', $job_type_id);
        }
        if (isset($category_id)) {
            $result = $result->where('jobs.category_id', '=', $category_id);
        }
        if (isset($keyword)) {
            $result->where(function ($query) use ($keyword) {
                $query->where('jobs.title', 'LIKE', '%' . $keyword . '%')
                      ->orwhere('jobs.description', 'LIKE', '%' . $keyword . '%')
                      ->orwhere('jobs.job_content', 'LIKE', '%' . $keyword . '%');
            });
        }

        $result = $result->where('jobs.release_start_date', '<=', now()->timestamp)
                        ->where('jobs.release_end_date', '>=', now()->timestamp);

        $data = array();
        $data['total_items'] = $result->count();

        $result = $result->orderBy('jobs.id', 'desc')
            ->offset($page_number)
            ->limit($page_limit)
            ->get();
        $data['data'] = $result;

        if (count($data['data']) > 0) {
            for ($i = 0; $i < count($data['data']); $i++) {
                $data['data'][$i]['main_image'] = JobFile::getMainImageJob($data['data'][$i]['id']);
                $data['data'][$i]['base_path'] = Job::getBasePathJob();
            }
        }

        return $data;
    }
    public static function detailForApplicant($id)
    {
        $query = Job::select(
            'jobs.id',
            'jobs.introduction_title',
            'jobs.store_name',
            'categories.category_name',
            'job_categories.job_category_name',
            'companies.company_name'
        );
        $result = $query
            ->leftjoin('categories', 'categories.id', '=', 'jobs.category_id')
            ->leftjoin('job_categories', 'job_categories.id', '=', 'jobs.job_category_id')
            ->leftjoin('companies', 'companies.id', '=', 'jobs.company_id')
            ->where('jobs.id', $id)
            ->first();
        return $result;
    }

    public static function getCompanyIdByJobId($job_id)
    {
        $query = Job::select(
            'jobs.company_id'
        );
        $result = $query
            ->where('jobs.id', $job_id)
            ->first();
        return $result;
    }

    public static function getBasePathJob()
    {
        $path = '';
        if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') {
            $path = 'https://' . $_SERVER['HTTP_HOST'] . '/upload/job/';
        } else {
            $path = 'http://' . $_SERVER['HTTP_HOST'] . '/upload/job/';
        }
        return $path;
    }
}
