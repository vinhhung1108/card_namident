<?php
namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;

class UserSearch extends User
{
    public $created_from;
    public $created_to;
    public $updated_from;
    public $updated_to;

    public function rules()
    {
        return [
            [['id','status'], 'integer'],
            [['username','email','full_name'], 'safe'],
            [['created_from','created_to','updated_from','updated_to'], 'safe'],
        ];
    }

    public function scenarios()
    {
        return Model::scenarios();
    }

    public function search($params, $formName = null)
    {
        $query = User::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort'  => ['defaultOrder' => ['id' => SORT_DESC]],
            // 'pagination' => ['pageSize' => 20],
        ]);

        $this->load($params, $formName);
        if (!$this->validate()) return $dataProvider;

        // Text filters
        $query->andFilterWhere(['id' => $this->id]);
        $query->andFilterWhere(['status' => $this->status]);
        $query->andFilterWhere(['like', 'username', $this->username]);
        $query->andFilterWhere(['like', 'email', $this->email]);
        $query->andFilterWhere(['like', 'full_name', $this->full_name]);

        // Date range helpers
        $toTs = function($str, $isEnd=false) {
            if (!$str) return null;
            // dd/mm/yyyy -> Y-m-d
            if (preg_match('~^\d{2}/\d{2}/\d{4}$~', $str)) {
                $dt = \DateTime::createFromFormat('d/m/Y', $str);
            } else {
                $dt = new \DateTime($str); // fallback: Y-m-d, etc.
            }
            if (!$dt) return null;
            if ($isEnd) $dt->setTime(23,59,59); else $dt->setTime(0,0,0);
            return $dt->getTimestamp();
        };

        // created_at range
        $cFrom = $toTs($this->created_from, false);
        $cTo   = $toTs($this->created_to,   true);
        if ($cFrom && $cTo) {
            $query->andWhere(['between','created_at',$cFrom,$cTo]);
        } elseif ($cFrom) {
            $query->andWhere(['>=','created_at',$cFrom]);
        } elseif ($cTo) {
            $query->andWhere(['<=','created_at',$cTo]);
        }

        // updated_at range
        $uFrom = $toTs($this->updated_from, false);
        $uTo   = $toTs($this->updated_to,   true);
        if ($uFrom && $uTo) {
            $query->andWhere(['between','updated_at',$uFrom,$uTo]);
        } elseif ($uFrom) {
            $query->andWhere(['>=','updated_at',$uFrom]);
        } elseif ($uTo) {
            $query->andWhere(['<=','updated_at',$uTo]);
        }

        return $dataProvider;
    }
}
