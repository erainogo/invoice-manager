<?php namespace App\Repositories;

use Illuminate\Support\Arr;

class BaseEloquentRepository {

    protected $validator = null;

    protected $model;

    protected $order = null;

    protected $dateParams = array('from' => null,'to' => null);


    /**
     * Find the model given an ID
     * @param $id
     * @return mixed
     */
    public function find($id) {
        return $this->model->find($id);
    }

    /**
     * Find the model given an ID
     * @param $id
     * @return mixed
     */
    public function findOrFail($id) {
        return $this->model->findOrFail($id);
    }

    /**
     * Find all models
     * @return mixed
     */
    public function findAll() {
        return $this->model->all();
    }

    /**
     * @param array $relations Relation to eager load
     * @param int $paginate
     * @return mixed
     */
    public function findAllPaginated($relations = array(), $paginate = 15)
    {
        $model = $this->model;
        if($this->order != null) {
            $model = $model->orderBy($this->order[0], $this->order[1]);
        }

        //eager load relations
        foreach($relations as $relation) {
            $model->with($relation);
        }

        return $model->paginate($paginate);
    }

    public function findIn(array $ids, $column = 'id')
    {
        return $this->model->whereIn($column, $ids)->get();
    }
    public function findInPaginate(array $ids, $column = 'id',$paginate=15)
    {
        return $this->model->whereIn($column, $ids)->paginate($paginate);
    }

    /**
     * find records with order DESC by given orderBy
     * @param array $ids
     * @param string $column
     * @return mixed
     */
    public function findInWithOrder(array $ids, $column = 'id', $orderBy = 'id')
    {
        return $this->model->whereIn($column, $ids)->orderBy($orderBy, 'DESC')->get();
    }

    /**
     * Update record with the given id and data
     * @param $id
     * @param $data
     * @return mixed
     */
    public function updateWithId($id, $data) {

        $model = $this->model->findOrFail($id);
        return $this->update($model, $data);
    }

    /**
     * Update record and return model
     * @param $id
     * @param $data
     * @return mixed
     */
    public function updateWithIdAndReturnModel($id, $data)
    {
        $model = $this->model->findOrFail($id);
        $this->update($model, $data);

        return $model;
    }

    /**
     * Update record and return model
     * @param $id
     * @param $data
     * @param string $key
     * @return mixed
     */
    public function updateWithExternalIdAndReturnModel($id, $data, $key = 'id')
    {
        $model = $this->model->where($key, $id)->first();

        $this->update($model, $data);

        return $model;
    }

    public function update($model, $data)
    {
        foreach ($data as $key => $value) {
            $model->{$key} = $value;
        }

        $model->save();

        return $model;
    }


    /**
     * Create record and return model
     * @param $data
     * @return mixed
     */
    public function createAndReturnModel($data)
    {
        $this->validate($data);
        $model =  $this->model->create($data);

        return $model;
    }

    /**
     * Create
     * @param $data
     * @return mixed
     */
    public function create($data) {
        $this->validate($data);
        return $this->model->create($data);
    }

    public function createNew($data) {
        return new $this->model($data);
    }

    public function updateOrCreate($input, $key = 'id')
    {
        // Instantiate new OR existing object
        if (! empty($input[$key])){
            $resource = $this->model->firstOrNew(array($key => $input[$key]));
        }
        else{
            $resource = $this->model; // Use a clone to prevent overwriting the same object in case of recursion
        }

        // Fill object with user input using Mass Assignment
        $resource->fill($input);

        // Save data to db
        if (! $resource->save()) return false;

        return $resource->toArray();
    }

    public function updateOrCreateAndReturnModel($input, $key = 'id')
    {
        // Instantiate new OR existing object
        if (! empty($input[$key])){
            $resource = $this->model->firstOrNew(array($key => $input[$key]));
        }
        else{
            $resource = $this->model; // Use a clone to prevent overwriting the same object in case of recursion
        }

        // Fill object with user input using Mass Assignment
        $resource->fill($input);

        // Save data to db
        if (! $resource->save()) return false;

        return $resource;
    }

    public function validate($input) {
        return $this->validator ? $this->validator->validateForCreation($input) : true;
    }

    public function validateUpdate($input) {
        return $this->validator ? $this->validator->validateForUpdate($input) : true;
    }

    public function setValidator($validator)
    {
        $this->validator = $validator;
        return $this;
    }

    /**
     * @return null
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * @param null $order
     */
    public function setOrder($order)
    {
        //do some validation on order
        if( ! is_array($order) ) {
            throw new \InvalidArgumentException("Order must be an array");
        }

        if( ! Arr::get($order, 0) ) {
            throw new \InvalidArgumentException("Order field not found");
        }

        if( ! Arr::get($order, 1) ) {
            $order[1] = 'ASC';
        }

        $this->order = $order;
    }


    public function setDateParams($from,$to)
    {
        $this->dateParams = array('from' => $from,'to' => $to);
    }
    public function getDateParams()
    {
        return $this->dateParams;
    }


    public function deleteById($id)
    {
        $model = $this->model->findOrFail($id);

        return $model->delete();
    }

    public function delete($model)
    {
        return $model->delete();
    }

    public function getOneByCriteria($column, $value)
    {
        return $this->model->where($column, $value)->first();
    }

    /**
     * Added in Laravel 4.2 !
     *
     * @param array $attributes
     * @param array $values
     * @return mixed
     */
    public function updateOrCreateLaravel(array $attributes, array $values = array())
    {
        //return;
        return $this->model->updateOrCreate($attributes, $values);
    }

    public function getValidator()
    {
        return $this->validator;
    }

    public function getBy($column, $value)
    {
        return $this->model->where($column, $value)->get();
    }

    public function getSelectedById($id, $select, $toArray=false)
    {
        $builder = $this->select($select)
            ->where($this->model->getKeyName(), $id);

        return $toArray ? $builder->first()->toArray() : $builder->first() ;
    }

    public function findWithRelations($id,$with = []) {
        return $this->model->with($with)->find($id);
    }

    public function findBy($column, $value)
    {
        return $this->model->where($column, $value)->first();
    }

    public function getBetweenWithCursor($field, $start, $end, $limit=null, $offset=null)
    {
        $query = $this->model->whereBetween($field, [$start, $end]);
        if($limit){
            $query->limit($limit);
        }

        if($offset){
            $query->offset($offset);
        }

        return $query->cursor();
    }

}