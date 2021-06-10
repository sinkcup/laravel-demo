pipeline {
  agent {
    docker {
      image 'laravelfans/laravel:6'
      reuseNode 'true'
      args '--net=host -v /var/run/docker.sock:/var/run/docker.sock -v /usr/bin/docker:/usr/bin/docker'
    }
  }
  stages {
    stage("检出") {
      steps {
        checkout([
          $class: 'GitSCM',
          branches: [[name: GIT_BUILD_REF]],
          userRemoteConfigs: [[
            url: GIT_REPO_URL,
            credentialsId: CREDENTIALS_ID
        ]]])
      }
    }
    stage('准备依赖') {
      steps {
        sh 'pecl install xdebug'
        sh 'docker-php-ext-enable xdebug'
        sh 'composer install'
        sh(script:'docker run -p 6379:6379 -d redis:5',   returnStdout: true)
        sh 'docker ps'
      }
    }
    stage('单元测试') {
      post {
        always {
          junit 'storage/test-results/junit.xml'
        }
        success {
          codingHtmlReport(name: '测试覆盖率报告', path: 'storage/reports/tests/')
        }
      }
      steps {
        sh 'touch database/database.sqlite'
        sh 'XDEBUG_MODE=coverage ./vendor/bin/phpunit --coverage-html storage/reports/tests/ --log-junit storage/test-results/junit.xml --coverage-text tests/'
      }
    }
    stage('生成 API 文档') {
      steps {
        sh 'php artisan l5-swagger:generate'
        codingReleaseApiDoc(apiDocId: '1', apiDocType: 'specificFile', resultFile: 'storage/api-docs/api-docs.json')
      }
    }
    stage('构建 Docker 镜像') {
      steps {
        script {
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
    }
  }
  environment {
    CODING_DOCKER_REG_HOST = "${CCI_CURRENT_TEAM}-docker.pkg.${CCI_CURRENT_DOMAIN}"
    CODING_DOCKER_IMAGE_NAME = "${env.PROJECT_NAME.toLowerCase()}/laravel-docker/laravel-demo"
  }
}
