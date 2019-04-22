<?php
/**
 * プロジェクトのブランチ別ワーキングツリーのパスを取得する
 */
function get_project_workingtree_dir($project_name, $branch_name) {
    $project_path = env('BD_DATA_DIR').'/projects/'.urlencode($project_name).'/branches/'.urlencode($branch_name);
    return $project_path;
}
