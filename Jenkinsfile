node {
  stage("检出") {
    checkout([
      $class: 'GitSCM',
      branches: [[name: GIT_BUILD_REF]],
      userRemoteConfigs: [[
        url: GIT_REPO_URL,
        credentialsId: CREDENTIALS_ID
    ]]])
  }
  stage('准备数据库') {
    sh 'docker network create bridge1'
    sh(script:'docker run --net bridge1 --name mysql -d -e "MYSQL_ROOT_PASSWORD=my-secret-pw" -e "MYSQL_DATABASE=test_db" mysql:5.7', returnStdout: true)
    sh(script:'docker run --net bridge1 --name redis -d redis:5', returnStdout: true)
  }
  docker.image('ecoding/php:8.0').inside("--net bridge1 -v \"${env.WORKSPACE}:/root/code\" -e 'APP_ENV=testing' -e 'DB_DATABASE=test_db'" +
      " -e 'DB_USERNAME=root' -e 'DB_PASSWORD=my-secret-pw' -e 'DB_HOST=mysql' -e 'REDIS_HOST=redis'" +
      " -e 'APP_KEY=base64:tbgOBtYci7i7cdx5RiFE3KZzUkRtJfbU3lbj5uPdL8U='") {
    sh 'composer install'

    stage('单元测试') {
      sh 'XDEBUG_MODE=coverage ./vendor/bin/phpunit --coverage-html storage/reports/tests/ --log-junit storage/test-results/junit.xml --coverage-text tests/'
      junit 'storage/test-results/junit.xml'
      codingHtmlReport(name: '测试覆盖率报告', path: 'storage/reports/tests/')
    }

    stage('生成 API 文档') {
      sh 'php artisan l5-swagger:generate'
      codingReleaseApiDoc(apiDocId: '1', apiDocType: 'specificFile', resultFile: 'storage/api-docs/api-docs.json')
    }
  }

  CODING_DOCKER_REG_HOST = "${CCI_CURRENT_TEAM}-docker.pkg.${CCI_CURRENT_DOMAIN}"
  CODING_DOCKER_IMAGE_NAME = "${env.PROJECT_NAME.toLowerCase()}/laravel-docker/laravel-demo"
  stage('构建 Docker 镜像') {
    if (env.TAG_NAME ==~ /.*/ ) {
      DOCKER_IMAGE_VERSION = "${env.TAG_NAME}"
    } else if (env.MR_SOURCE_BRANCH ==~ /.*/ ) {
      DOCKER_IMAGE_VERSION = "mr-${env.MR_RESOURCE_ID}-${env.GIT_COMMIT_SHORT}"
    } else {
      DOCKER_IMAGE_VERSION = "${env.BRANCH_NAME.replace('/', '-')}-${env.GIT_COMMIT_SHORT}"
    }
    // 本项目内的制品库已内置环境变量 CODING_ARTIFACTS_CREDENTIALS_ID，无需手动设置
    docker.withRegistry("https://${env.CCI_CURRENT_TEAM}-docker.pkg.coding.net", "${env.CODING_ARTIFACTS_CREDENTIALS_ID}") {
      docker.build("${CODING_DOCKER_IMAGE_NAME}:${DOCKER_IMAGE_VERSION}").push()
    }
  }
}
