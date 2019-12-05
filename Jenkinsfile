node {
    checkout([
        $class: 'GitSCM',
        branches: [[name: env.GIT_BUILD_REF]],
        userRemoteConfigs: [[url: env.GIT_REPO_URL, credentialsId: env.CREDENTIALS_ID]]
    ])
    codePath = sh(script: 'pwd', returnStdout: true).trim()
    sh 'docker network create bridge1'
    sh(script:'docker run --net bridge1 --name mysql -d -e "MYSQL_ROOT_PASSWORD=my-secret-pw" -e "MYSQL_DATABASE=test" mysql:5.7', returnStdout: true)
    sh(script:'docker run --net bridge1 --name redis -d redis:5', returnStdout: true)
    sh "docker login -u $DOCKER_USER -p $DOCKER_PASSWORD $DOCKER_SERVER"
    md5 = sh(script: "md5sum Dockerfile | awk '{print \$1}'", returnStdout: true).trim()
    imageAndTag = "${DOCKER_SERVER}${DOCKER_PATH_PREFIX}laravel-demo:dev-${md5}"
    dockerNotExists = sh(script: "DOCKER_CLI_EXPERIMENTAL=enabled docker manifest inspect $imageAndTag > /dev/null", returnStatus: true)
    def testImage = null
    if (dockerNotExists) {
        testImage = docker.build("$imageAndTag", "--build-arg APP_ENV=testing --build-arg SPEED=$SPEED ./")
        sh "docker push $imageAndTag"
    } else {
        testImage = docker.image(imageAndTag)
    }
    testImage.inside("--net bridge1 -v \"${codePath}:/var/www/laravel\" -e 'APP_ENV=testing' -e 'DB_DATABASE=test'" +
        " -e 'DB_USERNAME=root' -e 'DB_PASSWORD=my-secret-pw' -e 'DB_HOST=mysql' -e 'REDIS_HOST=redis'" +
        " -e 'APP_KEY=base64:tbgOBtYci7i7cdx5RiFE3KZzUkRtJfbU3lbj5uPdL8U='") {
        stage('prepare') {
            echo 'preparing...'
            sh "bash ./speed -s $SPEED composer"
            sh 'composer install'
            echo 'prepare done.'
        }
        stage('test') {
            echo 'testing...'
            sh 'env'
            sh './lint.sh'
            sh './phpunit.sh'
            junit 'junit.xml'
            echo 'test done.'
        }
    }
}
